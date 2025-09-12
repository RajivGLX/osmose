import { Component, EventEmitter, Input, Output, WritableSignal } from '@angular/core';
import { User } from '../../interface/user.interface';
import { AdminFormService } from '../services/admin-form.service';
import { ErrorHandler } from "../../shared/handlers/error.handler";
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatIconModule } from '@angular/material/icon';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { LoaderComponent } from '../../loader/loader.component';
import { CenterListService } from '../../center-list/services/center-list.service';
import { NgSelectModule } from '@ng-select/ng-select';
import { MultiLabelDirective } from '../../shared/directives/multi-label.directive';
import { MatOptionModule } from '@angular/material/core';
import { MatSelectModule } from '@angular/material/select';
import { RolePipe } from '../../utils/pipe/role.pipe';
import { Observable } from 'rxjs';
import { Center } from '../../interface/center.interface';

@Component({
    selector: 'app-admin-create',
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
        RolePipe
        ],
    templateUrl: './admin-create.component.html',
    styleUrl: './admin-create.component.sass'
})
export class AdminCreateComponent {

    @Output() goBackEvent = new EventEmitter<void>()

    create_admin_form = this.adminFormService.create_admin_form
    email_form = this.adminFormService.email_form
    loadingAdmin: WritableSignal<boolean> = this.adminFormService.loadingAdmin
    allCenter: Observable<Center[]> =  this.centerListService.allCenter$
    listAllRoles = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN_OSMOSE'];
    
    hide: boolean = true;
    errors : any = {};

    constructor(
        private adminFormService: AdminFormService,
        private centerListService: CenterListService,
        private errorHandler: ErrorHandler
    ) { }

    ngOnInit(): void {
        this.centerListService.getAllCenters()
        this.errorHandler.handleErrors(this.create_admin_form,this.errors)
    }

    sendNewUserData() {
        this.adminFormService.createAdmin()
        this.goBack()
    }

    goBack() {
        this.goBackEvent.emit()
    }

}
