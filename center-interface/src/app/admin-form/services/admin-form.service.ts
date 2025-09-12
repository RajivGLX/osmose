import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Injectable, signal, WritableSignal } from '@angular/core';
import { AbstractControl, FormBuilder, FormControl, FormGroup, ValidationErrors, Validators } from '@angular/forms';
import { LoginService } from '../../login/services/login.service';
import { confirmEqualValidators } from '../../shared/validators/confirmEqualValidators';
import { environment } from '../../../environment/environment';
import { User } from '../../interface/user.interface';
import { ToolsService } from '../../shared/services/tools.service';
import { Center } from '../../interface/center.interface';
import { AdminListService } from '../../admin-list/services/admin-list.service';

@Injectable({
    providedIn: 'root'
})
export class AdminFormService {

    loadingAdmin: WritableSignal<boolean> = signal(false)
    
    current_email: FormControl = new FormControl('')
    email_ctrl: FormControl = new FormControl('', [Validators.email])
    confirm_email_ctrl: FormControl = new FormControl('', [Validators.email])
    password_ctrl: FormControl = new FormControl('', [
        Validators.minLength(8),Validators.pattern('^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,}$')
    ])
    
    confirm_password_ctrl: FormControl = new FormControl('', (control: AbstractControl): ValidationErrors | null => {
        const passwordCtrl = this.password_ctrl;
        if (!passwordCtrl.value) return null;
        if (!control.value) return { required: true };
        if (control.value !== passwordCtrl.value) return { confirmEqual: true };

        return null;
    });
    current_password: FormControl = new FormControl('', (control: AbstractControl): ValidationErrors | null => {
        if (!this.password_ctrl.value) { return null }
        if (!control.value) { return { required: true } }
        return null;
    });

    password_form: FormGroup = this.fb.group(
        {
            current_password: this.current_password,
            new_password: this.password_ctrl,
            confirm_new_password: this.confirm_password_ctrl
        },
        {
            updateOn: 'blur'
        }
    );
    
    email_form: FormGroup = this.fb.group(
        {
            current_email: this.current_email,
            new_email: this.email_ctrl,
            confirm_new_email: this.confirm_email_ctrl
        },
        {
            validators: [confirmEqualValidators('new_email', 'confirm_new_email')],
            updateOn: 'blur'
        }
    )

    update_admin_form: FormGroup = this.fb.group({
        id: [null],
        lastname: [null, [Validators.required, Validators.minLength(2)]],
        firstname: [null, [Validators.required, Validators.minLength(2)]],
        center_array: [null, Validators.required],
        role_array: [null, Validators.required],
        email: this.email_form,
        password: this.password_form,
    })

    create_admin_form: FormGroup = this.fb.group({
        lastname: [null, [Validators.required, Validators.minLength(2)]],
        firstname: [null, [Validators.required, Validators.minLength(2)]],
        center_array: [[], Validators.required],
        role_array: [null, Validators.required],
        email: this.email_form,
    })

    constructor(
        private fb: FormBuilder, 
        private http: HttpClient, 
        private loginService: LoginService,
        private adminListService: AdminListService,
        private toolsService: ToolsService
    ) {
        this.password_ctrl.valueChanges.subscribe(() => {
            this.current_password.updateValueAndValidity();
            this.confirm_password_ctrl.updateValueAndValidity();
        });
    }

    updateAdminInfo() {
        this.loadingAdmin.set(true)
        this.http.post<{message : string, data : User, reload: boolean}>(environment.apiURL + '/api/update-info-admin', {
            'id': this.update_admin_form.get('id')?.value,
            'firstname': this.update_admin_form.get('firstname')?.value, 
            'lastname': this.update_admin_form.get('lastname')?.value,
            'email': this.email_form.get('new_email')?.value, 
            'current_password': this.password_form.get('current_password')?.value, 
            'new_password': this.password_form.get('new_password')?.value, 
            'role_array': [this.update_admin_form.get('role_array')?.value],
            'center_array': this.update_admin_form.get('center_array')?.value.map((center: Center) => center.id),
        }).subscribe({
            next: (response : {message: string, data: User, reload: boolean}) => {
                this.toolsService.openSnackBar('success',response.message)
                if(response.data.adminOsmose){
                    this.initializeForm(response.data)
                    this.loginService.getConnectedUser()
                }else{
                    if(response.reload){
                        this.loginService.logout()
                        this.toolsService.openSnackBar('success','Vos informations ont bien été modifié. Nous vous avons deconnecter, veuillez vous reconnecter avec vos nouveau identifiants')
                    }else{
                        this.loginService.getConnectedUser()
                    }
                }
                this.adminListService.updateAdminInList(response.data)
                this.initializeForm(response.data)
                this.loadingAdmin.set(false)
            },
            error: (response: HttpErrorResponse) => {
                this.loadingAdmin.set(false)
                console.log(response)
                this.toolsService.openSnackBar('error',response.error.message);
            }
        })
    }

    createAdmin() {
        this.http.post<{message : string, data : User, reload: boolean}>(environment.apiURL + '/api/create-admin', {
            'firstname': this.create_admin_form.get('firstname')?.value, 
            'lastname': this.create_admin_form.get('lastname')?.value,
            'email': this.email_form.get('new_email')?.value, 
            'role_array': [this.create_admin_form.get('role_array')?.value],
            'center_array': this.create_admin_form.get('center_array')?.value.map((center: Center) => center.id),
        }).subscribe({
            next: (response : {message: string, data: User, reload: boolean}) => {
                this.toolsService.openSnackBar('success',response.message)
                this.adminListService.addAdminToList(response.data)
                this.resetEmailForm()
            },
            error: (response: HttpErrorResponse) => {
                console.log(response)
                this.toolsService.openSnackBar('error',response.error.message)
            }
        })
    }

    // Initialise le formulaire avec les données de l'utilisateur
    initializeForm(userData: User): void {
        this.update_admin_form.get('id')?.patchValue(userData.id)
        this.update_admin_form.get('lastname')?.patchValue(userData.lastname)
        this.update_admin_form.get('firstname')?.patchValue(userData.firstname)
        this.update_admin_form.get('role_array')?.patchValue(userData.roles[0])
        this.current_email.patchValue(userData.email)
        if(userData.administrator){
            const centers = Array.isArray(userData.administrator.centers) ? userData.administrator.centers : Object.values(userData.administrator.centers)
            this.update_admin_form.get('center_array')?.patchValue(centers)
        }
        this.resetPasswordForm();
    }

    resetPasswordForm() {
        this.password_form.reset({
            current_password: '',
            new_password: '',
            confirm_new_password: ''
        });
    }

    resetEmailForm() {
        this.email_form.reset({
            current_email: '',
            new_email: '',
            confirm_new_email: ''
        });
    }

}
