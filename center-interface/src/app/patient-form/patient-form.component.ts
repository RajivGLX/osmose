import { CommonModule } from '@angular/common';
import { Component, Input, Output, EventEmitter } from '@angular/core';
import { PatientCreateComponent } from './patient-create/patient-create.component';
import { PatientUpdateComponent } from './patient-update/patient-update.component';
import { User } from '../interface/user.interface';
import { CenterListService } from '../center-list/services/center-list.service';
import { PatientFormService } from './services/patient-form.service';

@Component({
    selector: 'app-patient-form',
    standalone: true,
    imports: [
        CommonModule, 
        PatientUpdateComponent,
        PatientCreateComponent
    ],
    templateUrl: './patient-form.component.html',
    styleUrl: './patient-form.component.sass'
})
export class PatientFormComponent {
    
    @Output() closeVueDetaillee = new EventEmitter<boolean>(false)
    @Input() view!: string
    @Input() set userSelect (value: User) {
        if (value) {
            this.patientToUpdate = value
        }
    }
    
    patientToUpdate! : User
    
    constructor(
        private centerListService: CenterListService,
        private patientFormService : PatientFormService
    ) {
        if (!this.view){
            this.view = 'update' 
        }
    }

    ngOnInit(): void {
        this.centerListService.getAllCenters()
    }

    goBack() {
        this.closeVueDetaillee.emit(true)
    }
}
