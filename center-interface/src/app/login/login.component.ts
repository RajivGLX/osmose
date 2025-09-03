import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarHorizontalPosition, MatSnackBarVerticalPosition } from '@angular/material/snack-bar';
import { RouterLink } from '@angular/router';
import { Observable } from 'rxjs';
import { LoginService } from './services/login.service';
import { HttpClientModule } from '@angular/common/http';

@Component({
    selector: 'app-login',
    standalone: true,
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterLink,
        MatProgressSpinnerModule,
        MatFormFieldModule,
        MatInputModule,
        MatIconModule,
        HttpClientModule
    ],
    templateUrl: './login.component.html',
    styleUrl: './login.component.sass'
})

export class LoginComponent implements OnInit {

    constructor(
        private fb: FormBuilder, 
        private loginService: LoginService, 
        private _snackBar: MatSnackBar
    ) { }

    formValid = true;
    loader: boolean = false
    showAlertMessageSuccess: boolean = false;
    showAlertMessageError: boolean = false;
    loginForm!: FormGroup
    hide: boolean = true;

    loading: Observable<boolean> = this.loginService.loading$
    errorMsg: Observable<string> = this.loginService.errorMsg$
    successMsg: Observable<string> = this.loginService.successMsg$

    horizontalPosition: MatSnackBarHorizontalPosition = 'center';
    verticalPosition: MatSnackBarVerticalPosition = 'top';

    ngOnInit(): void {

        // Définition du formulaire de connexioon
        this.loginForm = this.fb.group({
            username: [null, [Validators.required, Validators.email]],
            password: [null, Validators.required]
        })
        setTimeout(() => {
            this.loginService.logout();
        }, 0);
    }


    // Envoi du formulaire de connexion
    sendLoginForm() {
        this.loginService.sendCredentials(this.loginForm.value)    
    }


}
