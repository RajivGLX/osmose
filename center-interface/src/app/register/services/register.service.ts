import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MatSnackBar, MatSnackBarHorizontalPosition, MatSnackBarVerticalPosition } from '@angular/material/snack-bar';
import { Router } from '@angular/router';
import { map } from 'rxjs';
import { environment } from '../../../environment/environment.development';
import { noms, numerosMobiles, numerosTelephone, prenoms, siren } from '../donnees/fake';
import { confirmEqualValidators } from '../../shared/validators/confirmEqualValidators';

@Injectable({
    providedIn: 'root'
})
export class RegisterService {



    constructor(private fb: FormBuilder, private http: HttpClient, private router: Router, private _snackBar: MatSnackBar) { }

    numero_siren: FormControl = new FormControl('', Validators.required)
    adresse_facturation_differente: FormControl = new FormControl(false)
    password_ctrl: FormControl = new FormControl('', Validators.required)
    confirm_password_ctrl: FormControl = new FormControl('', Validators.required)
    email_ctrl: FormControl = new FormControl('', [Validators.required, Validators.email])
    confirm_email_ctrl: FormControl = new FormControl('', [Validators.required, Validators.email])
    tel_fixe_ctrl: FormControl = new FormControl('', [Validators.required, Validators.minLength(10), Validators.maxLength(10)])
    tel_mobile_ctrl: FormControl = new FormControl('', [Validators.minLength(10), Validators.maxLength(10)])

    horizontalPosition: MatSnackBarHorizontalPosition = 'center';
    verticalPosition: MatSnackBarVerticalPosition = 'top';

    fake_siren: any[] = siren
    fake_numerosTelFixe: any[] = numerosTelephone
    fake_numerosTelMobile: any[] = numerosMobiles
    fake_nom: any[] = noms
    fake_prenom: any[] = prenoms
    // fake_adresse: any[] = adresses
    // fake_codePostaux: any[] = codesPostaux
    // fake_villes: any[] = villes

    emailForm: FormGroup = this.fb.group(
        {
            email: this.email_ctrl,
            confirm_email: this.confirm_email_ctrl
        },
        {
            validators: [confirmEqualValidators('email', 'confirm_email')],
            updateOn: 'blur'
        }
    )
    passwordForm: FormGroup = this.fb.group(
        {
            password: this.password_ctrl,
            confirm_password: this.confirm_password_ctrl
        },
        {
            validators: [confirmEqualValidators('password', 'confirm_password')],
            updateOn: 'blur'
        }
    )


    etudeForm: FormGroup = this.fb.group({
        CRPCEN: [null, Validators.required],
        siren: this.numero_siren,
        nom_etude: [null, Validators.required],
        forme_juridique: [null, Validators.required],
        tel_etude: this.tel_fixe_ctrl,
        adresse_etude: [null, Validators.required],
        cp_etude: [null, Validators.required],
        ville_etude: [null, Validators.required],
    })

    joursOuverture: FormGroup = this.fb.group({
        lundi: [true],
        mardi: [false],
        mercredi: [false],
        jeudi: [false],
        vendredi: [false],
        samedi: [false]
    })


    userForm: FormGroup = this.fb.group({
        civilite_utilisateur: [null, Validators.required],
        nom_utilisateur: [null, Validators.required],
        prenom_utilisateur: [null, Validators.required],
        tel_fixe: this.tel_fixe_ctrl,
        tel_mobile: this.tel_mobile_ctrl,
        email: this.emailForm,
        password: this.passwordForm,
    })

    facturationForm: FormGroup = this.fb.group({
        facturation_differente: this.adresse_facturation_differente,
        adresse_facturation: [null],
        cp_facturation: [null],
        ville_facturation: [null],
    })

    diversForm: FormGroup = this.fb.group({
        // logiciel_utilise: [null, Validators.required],
        // logiciel_utilise_precisions_autre: [null],
        // comment_connaitre_NRU: [null, Validators.required],
        // comment_connaitre_NRU_precisions_autre: [null],
        acceptation_alertes_mail: [null],
        acceptation_cgu: [null, Validators.required],
    })

    registerForm: FormGroup = this.fb.group({
        etude: this.etudeForm,
        user: this.userForm,
        facturation: this.facturationForm,
        divers: this.diversForm
    })

    $showEmailErrors = this.emailForm.statusChanges.pipe(
        map(status => status === 'INVALID'
            && this.email_ctrl.value
            && this.confirm_email_ctrl.value
            && this.emailForm.hasError('confirmEqual'))
    )

    $showPasswordErrors = this.registerForm.statusChanges.pipe(
        map(status => status === 'INVALID'
            && this.password_ctrl.value
            && this.confirm_password_ctrl.value
            && this.passwordForm.hasError('confirmEqual'))
    )

    generateRandomCRPCEN() {
        let crpCen = "0"; // Le préfixe CRP CEN commence généralement par 0
        for (let i = 0; i < 8; i++) {
            crpCen += Math.floor(Math.random() * 10); // Génère des chiffres aléatoires pour compléter le numéro
        }
        return crpCen;
    }

    getCrpCen() {
        const nombreDeNumeros = 20; // Nombre de numéros CRP CEN à générer
        const numerosCRPCEN = [];
        for (let i = 0; i < nombreDeNumeros; i++) {
            numerosCRPCEN.push(this.generateRandomCRPCEN());
        }
        return numerosCRPCEN
    }

    fake() {
        this.etudeForm.get('siren')?.patchValue(this.fake_siren[Math.round(Math.random() * 9)])
        this.etudeForm.get('CRPCEN')?.patchValue(this.getCrpCen()[Math.round(Math.random() * 19)])
        this.etudeForm.get('tel_fixe_etude')?.patchValue(this.fake_numerosTelFixe[Math.round(Math.random() * 19)])
        this.etudeForm.get('tel_mobile_etude')?.patchValue(this.fake_numerosTelMobile[Math.round(Math.random() * 19)])

        this.userForm.get('civilite_utilisateur')?.patchValue(1)
        this.userForm.get('prenom_utilisateur')?.patchValue(this.fake_prenom[Math.round(Math.random() * 19)])
        this.userForm.get('nom_utilisateur')?.patchValue(this.fake_nom[Math.round(Math.random() * 19)])
        this.userForm.get('tel_ligne_directe')?.patchValue(this.fake_numerosTelMobile[Math.round(Math.random() * 19)])

        const fakeMail = this.userForm.get('prenom_utilisateur')?.value.toLowerCase() + '.' + this.userForm.get('nom_utilisateur')?.value.toLowerCase() + '@notaires.fr'

        this.emailForm.get('email')?.patchValue(fakeMail)
        this.emailForm.get('confirm_email')?.patchValue(fakeMail)

        this.passwordForm.get('password')?.patchValue('123456')
        this.passwordForm.get('confirm_password')?.patchValue('123456')
        // console.log(this.registerForm)
    }

    /**
     * Envoi du formulaire d'inscription
     *
     * @param registerForm - Valeurs du formulaire d'inscription
     */
    sendRegisterForm(registerForm: FormGroup<any>) {
        this.http.post(environment.apiURL + '/register', registerForm.value).subscribe({
            next: (v: any) => {
                console.log(v)

                if (v.crpcen !== '') {
                    this.router.navigate(['/verify-crpcen/'])
                } else {
                    this.router.navigate(['/verify-account/' + v.token]);
                }
            },
            error: (e: any) => {
                console.log(e)
            }
        })
    }

    /**
     * Renvoi un mail d'activation de compte
     *
     * @param token - Token d'activation
     */
    resendMail(token: string) {
        this.http.post(environment.apiURL + '/resend-mail', { token }).subscribe({
            next: (v: any) => {
                console.log(v)
            },
            error: (e: any) => {
                console.log(e)
            }
        })
    }

    /**
     * Envoi un mail d'activation de compte
     *
     * @param token - Token d'activation
     * @returns
     */
    activateAccount(token: string) {
        return this.http.post(environment.apiURL + '/activate-account', { token });
    }

    /**
     * Affiche un message à l'utilisateur
     *
     * @param msg - Message
     * @param type - Type de message (success ou error)
     */
    openSnackBar(msg: string, type: string) {
        this._snackBar.open(msg, 'fermer', {
            horizontalPosition: this.horizontalPosition,
            verticalPosition: this.verticalPosition,
            duration: 10000,
            panelClass: type
        });
    }

}
