import { Injectable, signal, WritableSignal } from '@angular/core';
import { User } from '../../interface/user.interface';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { ToolsService } from '../../shared/services/tools.service';
import { PatientListService } from '../../patient-list/services/patient-list.service';
import { confirmPreciseValidators } from '../../shared/validators/confirmPreciseValidators';
import { environment } from '../../../environment/environment.development';
import { Observable } from 'rxjs';


@Injectable({
    providedIn: 'root'
})
export class PatientFormService {
    
    loadingPatientUpdate: WritableSignal<boolean> = signal(false)
    loadingPatientCreate: WritableSignal<boolean> = signal(false)

    precise_allergie_form: FormGroup = this.fb.group(
            {
                drug_allergies: [null],
                drug_allergie_precise: [null],
            },
            {validators: confirmPreciseValidators('drug_allergies', 'drug_allergie_precise'),updateOn: 'change'}
        )

    patient_form: FormGroup = this.fb.group({
        lastname: [null, [Validators.required, Validators.minLength(2)]],
        firstname: [null, [Validators.required, Validators.minLength(2)]],
        email: [null, [Validators.required, Validators.email]],
        checked: [null, [Validators.required]],
        phone: [null],
        type_dialysis: [null],
        medical_history: [null],
        dialysis_start_date: [null],
        vascular_access_type: [null],
        center: [null],
        drugAllergies: this.precise_allergie_form
    })

    listTypeDialysis = ['Hémodialyse', 'Dialyse péritonéale']
    listAccessSite = ['FAVN', 'FAVP', 'GPV', 'CVC']

    constructor(
        private fb: FormBuilder, 
        private http: HttpClient, 
        private patientListService: PatientListService,
        private toolsService: ToolsService
    ) {}

    getOnePatient(): Observable<User> {
        return this.http.get<User>(environment.apiURL + '/api/get-one-patient')
    }

    updatePatientInfo(patientToUpdate: User) {
        this.loadingPatientUpdate.set(true)
        let formToSend = this.handleFormForSend(this.patient_form, this.precise_allergie_form, patientToUpdate.id)
        this.http.post<{message : string, data : User}>(environment.apiURL + '/api/update-info-patient', formToSend).subscribe({
            next: (response : {message: string, data: User}) => {
                this.toolsService.openSnackBar(response.message, true)
                this.initializeForm(response.data)
                this.initializeForm(response.data)
                this.patientListService.updatePatientInList(response.data)
                this.loadingPatientUpdate.set(false)
            },
            error: (response: HttpErrorResponse) => {
                this.loadingPatientUpdate.set(false)
                this.toolsService.openSnackBar(response.error.message, false);
                console.log(response)
            }
        })
    }

    createPatient() {
        let formToSend = this.handleFormForSend(this.patient_form, this.precise_allergie_form)
        this.http.post<{message : string, data : User}>(environment.apiURL + '/api/create-patient', formToSend).subscribe({
            next: (response : {message: string, data: User}) => {
                this.toolsService.openSnackBar(response.message, true)
                this.patientListService.addPatientToList(response.data)
            },
            error: (response: HttpErrorResponse) => {
                console.log(response)
                this.toolsService.openSnackBar(response.error.message, false);
            }
        })
    }

    initializeForm(patientToUpdate: User): void {
        const dialysisStartDate = patientToUpdate.patient.dialysis_start_date
            ? this.formatDate(patientToUpdate.patient.dialysis_start_date)
            : null;

        this.patient_form.patchValue({
            ...patientToUpdate.patient,
            dialysis_start_date: dialysisStartDate
        });

        this.precise_allergie_form?.patchValue(patientToUpdate.patient)
        this.patient_form?.patchValue(patientToUpdate)
        
        console.log('patientToUpdate : ', patientToUpdate)
    }

    resetAllForms() {
        this.patient_form.reset()
        this.precise_allergie_form.reset()
    }

    private formatDate(date: string | Date): string {
        const d = new Date(date);
        const month = ('0' + (d.getMonth() + 1)).slice(-2);
        const day = ('0' + d.getDate()).slice(-2);
        const year = d.getFullYear();
        return `${year}-${month}-${day}`;
    }

    private handleFormForSend(
        patient_form: FormGroup, 
        precise_allergie_form: FormGroup,
        idUser: number = 0
    ) {
        return {
            'idUser': idUser,
            'firstname': patient_form.get('firstname')?.value, 
            'lastname': patient_form.get('lastname')?.value,
            'email': patient_form.get('email')?.value,
            'checked': patient_form.get('checked')?.value,
            'center': patient_form.get('center')?.value ? patient_form.get('center')?.value.id : null,
            'phone': patient_form.get('phone')?.value,
            'type_dialysis': patient_form.get('type_dialysis')?.value,
            'medical_history': patient_form.get('medical_history')?.value,
            'dialysis_start_date': patient_form.get('dialysis_start_date')?.value,
            'vascular_access_type': patient_form.get('vascular_access_type')?.value,
            'drug_allergies': precise_allergie_form.get('drug_allergies')?.value,
            'drug_allergie_precise': precise_allergie_form.get('drug_allergie_precise')?.value
        }
    }
}
