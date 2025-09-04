import { CommonModule } from '@angular/common';
import { Component, ElementRef, EventEmitter, Input, Output, Type, ViewChild, WritableSignal } from '@angular/core';
import { ErrorHandler } from "../../shared/handlers/error.handler";
import { ReactiveFormsModule } from '@angular/forms';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { LoaderComponent } from '../../loader/loader.component';
import { NgSelectModule } from '@ng-select/ng-select';
import { MultiLabelDirective } from '../../shared/directives/multi-label.directive';
import { MatOptionModule } from '@angular/material/core';
import { MatSelectModule } from '@angular/material/select';
import { User } from '../../interface/user.interface';
import { Center } from '../../interface/center.interface';
import { Observable } from 'rxjs';
import { PatientFormService } from '../services/patient-form.service';
import { CenterListService } from '../../center-list/services/center-list.service';
import { MatRadioModule } from '@angular/material/radio';
import { TypeDialysis } from '../../utils/pipe/type-dialysis.pipe';

@Component({
    selector: 'app-patient-update',
    standalone: true,
    imports: [
        CommonModule, 
        ReactiveFormsModule, 
        MatFormFieldModule, 
        MatInputModule, 
        MatIconModule, 
        MatCheckboxModule, 
        LoaderComponent,
        NgSelectModule,
        MultiLabelDirective,
        MatOptionModule,
        MatSelectModule,
        MatRadioModule,
        TypeDialysis,
    ],
    templateUrl: './patient-update.component.html',
    styleUrl: './patient-update.component.sass'
})
export class PatientUpdateComponent {
    
    @Output() goBackEvent = new EventEmitter<void>()
    @Input() patientToUpdate!: User
    @ViewChild('drugAllergiePreciseInput') drugAllergiePreciseInput!: ElementRef
    @ViewChild('renalFailureOtherInput') renalFailureOtherInput!: ElementRef


    patient_form = this.patientFormService.patient_form
    precise_allergie_form = this.patientFormService.precise_allergie_form
    renal_failure_form = this.patientFormService.renal_failure_form

    allCenter: Observable<Center[]> =  this.centerListService.allCenter$

    loadingPatient: WritableSignal<boolean> = this.patientFormService.loadingPatientUpdate
    
    hide: boolean = true
    errors : any = {}

    listRenalFailure = this.patientFormService.listRenalFailure
    listTypeDialysis = this.patientFormService.listTypeDialysis
    listAccessSite = this.patientFormService.listAccessSite

    constructor(
        private patientFormService: PatientFormService,
        private centerListService: CenterListService,
        private errorHandler: ErrorHandler
    ) {}

    ngOnInit(): void {
        // Récupère les informations de l'utilisateur pour le formulaire de modif
        this.patientFormService.initializeForm(this.patientToUpdate)
        this.errorHandler.handleErrors(this.patient_form,this.errors)
        this.errorHandler.handleErrors(this.precise_allergie_form,this.errors)
        this.errorHandler.handleErrors(this.renal_failure_form,this.errors)
        
        this.valueChangeFocus()
    }

    // Envoi les modifications concernant l'utilisateur
    sendUpdateUserData() {
        console.log(this.patientToUpdate)   
        this.patientFormService.updatePatientInfo(this.patientToUpdate)
    }
    
    valueChangeFocus() {
        this.precise_allergie_form.get('drug_allergies')?.valueChanges.subscribe(value => {
            if (value === true) this.drugAllergiePreciseInput.nativeElement.focus()
        })
        this.renal_failure_form.get('renal_failure')?.valueChanges.subscribe(value => {
            if (value === 'Autre') this.renalFailureOtherInput.nativeElement.focus()
        })
    }

    clearDialysisStartDate() {
        this.patient_form.get('dialysis_start_date')?.setValue(null);
    }

    goBack() {
        this.patientFormService.resetAllForms()
        this.goBackEvent.emit()
    }
}
