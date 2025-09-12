import { Injectable } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { confirmEqualValidators } from '../../../../shared/validators/confirmEqualValidators';
import { environment } from '../../../../../environment/environment';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import {ErrorHandler} from "../../../../shared/handlers/error.handler";
import { ToolsService } from '../../../../shared/services/tools.service';
import { User } from '../../../../interface/user.interface';
import { GestionUserService } from '../../services/gestion-user.service';
import { Center } from '../../../../interface/center.interface';

@Injectable({
  providedIn: 'root'
})
export class PopupAddUserService {

	constructor(
		private fb: FormBuilder, 
		private http: HttpClient,
		private toolsService: ToolsService,
		private errorHandler : ErrorHandler,
		private gestionUserService : GestionUserService
	) { }

	email_ctrl: FormControl = new FormControl('', [Validators.required, Validators.email])
	confirm_email_ctrl: FormControl = new FormControl('', [Validators.required, Validators.email])

	emailForm !: FormGroup;
	diversForm !: FormGroup;
	addUserForm!: FormGroup;
	messageErrors : any = {};

	initialiseMainForm() {
		this.addUserForm = this.fb.group({
		lastname: [null, [Validators.required,Validators.minLength(2)]],
		firstname: [null, [Validators.required,Validators.minLength(2)]],
		email: this.emailForm,
		divers: this.diversForm,
		})
		this.errorHandler.handleErrors(this.addUserForm,this.messageErrors);
	}
	initialiseSecondaryForms() {
		this.initialiseEmailForm();
		this.initialiseDiversForm();
	}

	private initialiseEmailForm() {

		this.emailForm = this.fb.group(
			{
				email: this.email_ctrl,
				confirm_email: this.confirm_email_ctrl
			},
			{
				validators: [confirmEqualValidators('email', 'confirm_email')],
				updateOn: 'blur'
			}
		)
	}

	private initialiseDiversForm() {
		this.diversForm = this.fb.group({
			acceptation_cgu: [null, Validators.required],
		})
	}

	onAddNewUser(center: Center) {
		this.http.post<{message : string, data : User, reload: boolean}>(environment.apiURL + '/api/create-admin', {
			'firstname': this.addUserForm.get('firstname')?.value, 
			'lastname': this.addUserForm.get('lastname')?.value,
			'email': this.emailForm.get('email')?.value, 
			'role_array': ['ROLE_ADMIN'],
			'center_array': [center.id],
		}).subscribe({
			next: (response : {message: string, data: User, reload: boolean}) => {
				this.toolsService.openSnackBar('success',response.message)
				this.gestionUserService.addAdminToList(response.data)
			},
			error: (response: HttpErrorResponse) => {
				console.log(response)
				this.toolsService.openSnackBar('error',response.error.message)
			}
		})
	}

}

