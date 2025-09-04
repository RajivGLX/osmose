import { Component, Output, EventEmitter, Input, ElementRef, ViewChild, WritableSignal } from '@angular/core';
import { User } from '../../interface/user.interface';
import { Center } from '../../interface/center.interface';
import { Observable } from 'rxjs';
import { PatientFormService } from '../services/patient-form.service';
import { CenterListService } from '../../center-list/services/center-list.service';
import { ErrorHandler } from '../../shared/handlers/error.handler';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatIconModule } from '@angular/material/icon';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { LoaderComponent } from '../../loader/loader.component';
import { NgSelectModule } from '@ng-select/ng-select';
import { MultiLabelDirective } from '../../shared/directives/multi-label.directive';
import { MatOptionModule } from '@angular/material/core';
import { MatSelectModule } from '@angular/material/select';
import { MatRadioModule } from '@angular/material/radio';
import { TypeDialysis } from '../../utils/pipe/type-dialysis.pipe';

@Component({
    selector: 'app-patient-create',
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
    templateUrl: './patient-create.component.html',
    styleUrl: './patient-create.component.sass'
})
export class PatientCreateComponent {
        @Output() goBackEvent = new EventEmitter<void>()
        @ViewChild('drugAllergiePreciseInput') drugAllergiePreciseInput!: ElementRef
        @ViewChild('renalFailureOtherInput') renalFailureOtherInput!: ElementRef
    
    
        patient_form = this.patientFormService.patient_form
        precise_allergie_form = this.patientFormService.precise_allergie_form
        renal_failure_form = this.patientFormService.renal_failure_form
    
        allCenter: Observable<Center[]> =  this.centerListService.allCenter$
    
        loadingPatient: WritableSignal<boolean> = this.patientFormService.loadingPatientCreate
        
        hide: boolean = true
        errors : any = {}
    
        listRenalFailure = this.patientFormService.listRenalFailure
        listTypeDialysis = this.patientFormService.listTypeDialysis
        listAccessSite = this.patientFormService.listAccessSite
    
        constructor(
            private patientFormService: PatientFormService,
            private centerListService: CenterListService,
            private errorHandler: ErrorHandler
        ) {
            this.patientFormService.resetAllForms()
        }
    
        ngOnInit(): void {
            // Récupère les informations de l'utilisateur pour le formulaire de modif
            this.errorHandler.handleErrors(this.patient_form,this.errors)
            this.errorHandler.handleErrors(this.precise_allergie_form,this.errors)
            this.errorHandler.handleErrors(this.renal_failure_form,this.errors)
            
            this.valueChangeFocus()
        }
    
        // Envoi les modifications concernant l'utilisateur
        sendUpdateUserData() {
            this.patientFormService.createPatient()
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
