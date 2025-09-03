import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { Router } from '@angular/router';
import { LoginService } from '../services/login.service';

@Component({
    selector: 'app-forgot-password',
    standalone: true,
    imports: [CommonModule, FormsModule, ReactiveFormsModule, MatFormFieldModule, MatInputModule, MatIconModule],
    templateUrl: './forgot-password.component.html',
    styleUrl: './forgot-password.component.sass'
})

export class ForgotPasswordComponent implements OnInit {
	forgotPasswordForm!: FormGroup;
	message: string = "";
	isLinkSend: boolean = false;

    constructor(private fb: FormBuilder, private loginService: LoginService, private router: Router) { }

	ngOnInit(): void {
		// Définition du form de mdp oublié
		this.forgotPasswordForm = this.fb.group({
			email: [null, [Validators.required, Validators.email]]
		});
	}

	// Envoi du form de mdp oublié
	sendForgotPasswordForm() {
		this.loginService.sendMailForgotPassword(this.forgotPasswordForm.value).subscribe({
			next: (v: any) => {
				this.message = v.message;
				this.isLinkSend = true;
			},
			error: (e: any) => {
				this.message = e.error.message;
			}
		});
	}

	// Redirige vers la page de connexion
	goBack() {
		this.router.navigateByUrl('/login')
	}
}
