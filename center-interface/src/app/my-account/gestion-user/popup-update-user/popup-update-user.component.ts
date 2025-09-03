import {ChangeDetectionStrategy, Component, Inject, OnInit} from '@angular/core';
import {User} from '../../../interface/user.interface';
import {MAT_DIALOG_DATA, MatDialog} from '@angular/material/dialog';
import {FormGroup, ReactiveFormsModule} from '@angular/forms';
import {CommonModule} from '@angular/common';
import {MatError, MatFormField, MatInput, MatLabel} from '@angular/material/input';
import {MatCheckbox} from '@angular/material/checkbox';
import {RolePipe} from '../../../utils/pipe/role.pipe';
import {LoaderComponent} from '../../../loader/loader.component';
import {MatRadioButton} from '@angular/material/radio';
import {JsonResponseInterface} from '../../../shared/interfaces/json-response-interface';
import {HttpErrorResponse} from "@angular/common/http";
import {ErrorHandler} from "../../../shared/handlers/error.handler";
import { PopupUpdateUserService } from './services/popup-update-user.service';

@Component({
    selector: 'app-popup-update-user',
    standalone: true,
    imports: [CommonModule, ReactiveFormsModule, MatInput, MatFormField, MatLabel, MatCheckbox, RolePipe, LoaderComponent, MatRadioButton, MatError],
    changeDetection: ChangeDetectionStrategy.OnPush,
    templateUrl: './popup-update-user.component.html',
    styleUrl: './popup-update-user.component.sass'
})
export class PopupUpdateUserComponent implements OnInit {
    user !: User;
    updateUserForm !:FormGroup;
    loading = false;
    listeRoles !: Array<string> ;
    messageErrors: any = {};



    constructor(
        @Inject(MAT_DIALOG_DATA) public data: User, 
        private dialog: MatDialog,
        private popupUpdateUserService: PopupUpdateUserService,
        // private gestionUserService: GestionUtilisateursService,
        private errorHandler: ErrorHandler
    ) { }

    ngOnInit(): void {
        this.user = this.data;
        this.updateUserForm = this.popupUpdateUserService.updateUserFormInitialise(this.user);
        console.log(this.updateUserForm);
        this.listeRoles = this.popupUpdateUserService.getListeRoles();

        this.errorHandler.handleErrors(this.updateUserForm,this.messageErrors);
    }


    addRole(role: string): void {
        this.popupUpdateUserService.onAddingRole(role);
    }

    submitUpdateForm() {

        this.loading = true;
        this.popupUpdateUserService.updateUserInfos().subscribe({
            next: (data: JsonResponseInterface) => {
                this.popupUpdateUserService.openSnackBar(data.message, data.success);
                this.closeModal();
                // this.gestionUserSer.getListeUsersCenter(true);
            },
            error: (err: HttpErrorResponse) => {
                console.log(err)
                err.error.message.forEach((erreur: string) => {
                    this.popupUpdateUserService.openSnackBar(erreur, false);
                    this.loading = false;
                })

            }
        })

    }

    closeModal() {
        this.dialog.closeAll();
        this.messageErrors ={}
    }

}
