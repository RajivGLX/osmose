import { CommonModule } from '@angular/common';
import {Component, OnInit, signal, WritableSignal} from '@angular/core';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { Router } from '@angular/router';
import { LoginService } from '../services/login.service';
import {MatProgressSpinner} from "@angular/material/progress-spinner";

@Component({
    selector: 'app-forgot-password',
    standalone: true,
    imports: [CommonModule, FormsModule, ReactiveFormsModule, MatFormFieldModule, MatInputModule, MatIconModule, MatProgressSpinner],
    templateUrl: './forgot-password.component.html',
    styleUrl: './forgot-password.component.sass'
})

export class ForgotPasswordComponent implements OnInit {
	forgotPasswordForm!: FormGroup;
	message: string = "";
	isLinkSend: boolean = false;

    loading : WritableSignal<boolean> = signal(false)

    validForgotPassForm: boolean = false

    constructor(private fb: FormBuilder, private loginService: LoginService, private router: Router) { }

	/**
     * Méthode appelée automatiquement lors de l'initialisation du composant.
     *
     * Cette méthode initialise le formulaire pour la fonctionnalité de mot de passe oublié
     * en définissant un groupe de contrôles avec un champ email obligatoire et valide.
     * Elle surveille également les modifications de statut du formulaire pour mettre à jour
     * une propriété indiquant si le formulaire est valide ou non.
     *
     * @return {void} Ne retourne pas de valeur.
     */
    ngOnInit(): void {
		// Définition du form de mdp oublié
		this.forgotPasswordForm = this.fb.group({
			email: [null, [Validators.required, Validators.email]]
		});
        this.forgotPasswordForm.statusChanges.subscribe(isValid => isValid === 'VALID' ? this.validForgotPassForm = true : this.validForgotPassForm = false)

    }

	// Envoi du form de mdp oublié
	sendForgotPasswordForm() {
        this.loading.set(true);
		this.loginService.sendMailForgotPassword(this.forgotPasswordForm.value).subscribe({
			next: (v: any) => {
                this.loading.set(false);
				this.message = v.message;
				this.isLinkSend = true;
			},
			error: (e: any) => {
                this.loading.set(false);
				this.message = 'Une erreur est survenue'
			}
		});
	}

	// Redirige vers la page de connexion
	goBack() {
		this.router.navigateByUrl('/login')
	}
}
