import { Dialog, DIALOG_DATA } from '@angular/cdk/dialog'
import { CommonModule } from '@angular/common'
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Inject, OnInit} from '@angular/core'
import { ReactiveFormsModule } from '@angular/forms'
import { MatError, MatFormField, MatInput, MatLabel } from '@angular/material/input'
import { MatCheckbox } from '@angular/material/checkbox'
import { LoaderComponent } from '../../../loader/loader.component'
import { PopupAddUserService } from './services/popup-add-user.service'
import { Center } from '../../../interface/center.interface'

@Component({
    selector: 'app-popup-add-user',
    standalone: true,
    imports: [
        CommonModule, 
        ReactiveFormsModule, 
        MatInput, 
        MatFormField, 
        MatLabel, 
        MatError, 
        MatCheckbox, 
        LoaderComponent
    ],
    templateUrl: './popup-add-user.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    styleUrl: './popup-add-user.component.sass'
})
export class PopupAddUserComponent implements OnInit {

    loading = false
    errors: any = this.popupAddUserService.messageErrors
    centers: Center[] = [];  // Propriété pour stocker les centres

    constructor(
        private dialog: Dialog, 
        public popupAddUserService: PopupAddUserService,
        @Inject(DIALOG_DATA) public data: { centers: Center[] },
        private cd: ChangeDetectorRef
    ) { }


    ngOnInit(): void {
        this.centers = this.data.centers
        this.popupAddUserService.initialiseSecondaryForms()
        this.popupAddUserService.initialiseMainForm()
        // Déclencher la détection de changements si OnPush est utilisé
        this.cd.detectChanges();
    }


    submitAddForm() {
        this.popupAddUserService.onAddNewUser(this.centers[0])
        this.closeModal()
    }


    closeModal() {
        this.dialog.closeAll();
        this.popupAddUserService.messageErrors = {}
    }

}
