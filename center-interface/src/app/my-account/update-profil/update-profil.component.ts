import { Component, EventEmitter, Input, Output, WritableSignal } from '@angular/core';
import { User } from '../../interface/user.interface';
import { ErrorHandler } from "../../shared/handlers/error.handler";
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatIconModule } from '@angular/material/icon';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { LoaderComponent } from '../../loader/loader.component';
import { CenterListService } from '../../center-list/services/center-list.service';
import { Center } from '../../interface/center.interface';
import { NgSelectModule } from "@ng-select/ng-select";
import { Observable } from 'rxjs';
import { MatOptionModule } from '@angular/material/core';
import { MatSelectModule } from '@angular/material/select';
import { AdminFormService } from '../../admin-form/services/admin-form.service';
import { MatIconButton } from '@angular/material/button';


@Component({
    selector: 'app-update-profil',
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
        MatOptionModule,
        MatSelectModule,
        MatIconButton
    ],
    templateUrl: './update-profil.component.html',
    styleUrl: './update-profil.component.sass'
})
export class UpdateProfilComponent {

    @Input() adminToUpdate!: Observable<User | null>

    update_admin_form = this.adminFormService.update_admin_form
    email_form = this.adminFormService.email_form
    password_form = this.adminFormService.password_form
    allCenter: Observable<Center[]> =  this.centerListService.allCenter$
    listAllRoles = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN_DIALYZONE'];

    
    loadingAdmin: WritableSignal<boolean> = this.adminFormService.loadingAdmin
    
    hide: boolean = true;
    errors : any = {};

    constructor(
        private adminFormService: AdminFormService,
        private centerListService: CenterListService,
        private errorHandler: ErrorHandler
    ) { }

    ngOnInit(): void {
        console.log('adminToUpdate',this.adminToUpdate)
        // Récupère les informations de l'utilisateur pour le formulaire de modif
        this.adminToUpdate.subscribe(user => {
            if (user) {
                this.adminFormService.initializeForm(user);
            }
        });
        this.errorHandler.handleErrors(this.update_admin_form,this.errors)
    }

    // Envoi les modifications concernant l'utilisateur
    sendUpdateUserData() {
        this.adminFormService.updateAdminInfo()
    }

}
