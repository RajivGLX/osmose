import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ActivatedRoute, ParamMap, Params, Router } from '@angular/router';
import { confirmEqualValidators } from '../../shared/validators/confirmEqualValidators';
import { LoginService } from '../services/login.service';
import { RegisterService } from '../../register/services/register.service';

@Component({
	selector: 'app-reset-password',
	standalone: true,
	imports: [FormsModule, ReactiveFormsModule, MatFormFieldModule, MatIconModule, MatInputModule],
	templateUrl: './reset-password.component.html',
	styleUrl: './reset-password.component.sass'
})

export class ResetPasswordComponent implements OnInit {
	firstHiddenInput: boolean = true;
	secondHiddenInput: boolean = true;
	resetPasswordForm!: FormGroup;
	token: string = "";
	tokenActivateAccount: string = "";
	errorMessage = "";

	constructor(private fb: FormBuilder, private route: ActivatedRoute, private loginService: LoginService, private _snackbar: MatSnackBar, private router: Router, private _snackBar: MatSnackBar, private registerService: RegisterService) { }

	ngOnInit(): void {
		// Définition du formulaire de changement de mdp
		this.resetPasswordForm = this.fb.group({
			newPassword: ['', [Validators.required]],
			confirmNewPassword: ['', [Validators.required]]
		}, {
			validators: [confirmEqualValidators('newPassword', 'confirmNewPassword')],
		});

		// Récupération du token de changement de mdp
		this.route.params.subscribe({
			next: (v: Params) => {
				this.token = v['token'];

				this.tokenActivateAccount = v['activateAccount'];

				console.log(this.tokenActivateAccount);
			}
		});
		if (this.tokenActivateAccount !== 'reset') {
			this.registerService.activateAccount(this.tokenActivateAccount).subscribe({
				next: (v: any) => {
					this.openSnackBar(v.message, true)
				},
				error: (e: Error) => {
					this.openSnackBar(e.message, true)
				}
			});

		}

	}
	openSnackBar(msg: string, type: boolean) {
		this._snackBar.open(msg, 'fermer', {
			horizontalPosition: 'center',
			verticalPosition: 'top',
			duration: 6000,
			panelClass: type == false ? 'error' : 'success'
		});
	}

	// Envoi du formulaire
	onSubmit() {
		// Vérifie si le formulaire est valide
		if (this.resetPasswordForm.valid) {
			this.loginService.resetPassword(this.token, this.resetPasswordForm.value).subscribe({
				next: (v: any) => {
					this._snackbar.open(v.message + 'Vous allez être redirigé vers la page de connexion', '', { duration: 4000 });
					setTimeout(() => {
						this.router.navigateByUrl('/login')
					}, 5000);
				},
				error: (e: any) => {
					this.errorMessage = e.error.message;
				}
			});
		} else { // Sinon affiche le message d'erreur
			this.errorMessage = 'Les mots de passe doivent être identiques';
		}
	}
}
