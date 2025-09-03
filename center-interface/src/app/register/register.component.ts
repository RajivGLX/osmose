import { CommonModule } from '@angular/common';
import { Component, OnInit, ViewChild } from '@angular/core';
import { AbstractControl, FormControl, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatRadioModule } from '@angular/material/radio';
import { MatSelectModule } from '@angular/material/select';
import { MatStepper, MatStepperModule } from '@angular/material/stepper';
import { RouterLink } from '@angular/router';
import { BehaviorSubject, Observable, combineLatest, map } from 'rxjs';
import { commentConnuNRU } from './donnees/commentConnuNru';
import { formesJuridiques } from './donnees/formeJuridique';
import { logicielUtilises } from './donnees/logiciel';
import { RegisterService } from './services/register.service';
import { villeFromApi } from '../urbanisme/interface/villeFromApi.interface';

@Component({
    selector: 'app-register',
    standalone: true,
    imports: [
        CommonModule,
        ReactiveFormsModule,
        FormsModule,
        MatButtonModule,
        MatFormFieldModule,
        MatInputModule,
        MatIconModule,
        MatCheckboxModule,
        MatRadioModule,
        MatSelectModule,
        MatStepperModule,
        MatStepper,
        RouterLink
    ],
    templateUrl: './register.component.html',
    styleUrl: './register.component.sass'
})
export class RegisterComponent implements OnInit {

    constructor(private registerService: RegisterService) { }

    hide: boolean = true
    registerForm: FormGroup = this.registerService.registerForm
    adresse_facturation_differente: FormControl = this.registerService.adresse_facturation_differente
    numero_siren: FormControl = this.registerService.numero_siren

    listeFormesJuridiques: any[] = formesJuridiques
    listeLogicielsUtilises: any[] = logicielUtilises
    listeCommentConnuNRU: any[] = commentConnuNRU

    etudeForm: FormGroup = this.registerService.etudeForm
    userForm: FormGroup = this.registerService.userForm
    facturationForm: FormGroup = this.registerService.facturationForm
    diversForm: FormGroup = this.registerService.diversForm

    emailForm: FormGroup = this.registerService.emailForm
    email_ctrl: FormControl = this.registerService.email_ctrl
    confirm_email_ctrl: FormControl = this.registerService.confirm_email_ctrl

    passwordForm: FormGroup = this.registerService.passwordForm
    password_ctrl: FormControl = this.registerService.password_ctrl
    confirm_password_ctrl: FormControl = this.registerService.confirm_password_ctrl

    tel_fixe_ctrl: FormControl = this.registerService.tel_fixe_ctrl
    tel_mobile_ctrl: FormControl = this.registerService.tel_mobile_ctrl

    horairesForm: FormGroup = this.registerService.horairesForm

    joursOuverture: FormGroup = this.registerService.joursOuverture

    lesJoursCool = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi']

    validRegisterForm: boolean = false
    validEtudeForm: boolean = false
    validUserForm: boolean = false
    validFacturationForm: boolean = true
    validDiversForm: boolean = false

    $showEmailErrors: Observable<boolean> = this.registerService.$showEmailErrors
    $showPasswordErrors: Observable<boolean> = this.registerService.$showPasswordErrors

    showLogicielPrecisionAutre: boolean = false
    showConnaitrePrecisionAutre: boolean = false

    isLinear = true;

    doublonCrpCen: boolean = false;

    @ViewChild('stepper') myStepper!: MatStepper;

    totalStep: number = 2;
    private stepChangeSubject = new BehaviorSubject<number>(1);
    currentStep$ = this.stepChangeSubject.asObservable();

    currentStepPercentage$ = combineLatest([this.currentStep$]).pipe(
        map(([currentStep]) => (currentStep / this.totalStep) * 100)
    );
    listeVilles : villeFromApi[] = [];

    ngOnInit(): void {
        this.numero_siren.valueChanges.subscribe(() => this.getSiren())

        this.adresse_facturation_differente.valueChanges.pipe(
            map((value) => {
                if (value === true) {
                    this.setFacturationDiffrenteValidators()
                } else {
                    this.facturationForm.get('adresse_facturation')!.reset();
                    this.facturationForm.get('cp_facturation')!.reset();
                    this.facturationForm.get('ville_facturation')!.reset();
                    this.clearFacturationDiffrenteValidators()
                }
            })).subscribe()

        this.etudeForm.statusChanges.subscribe(isValid => isValid === 'VALID' ? this.validEtudeForm = true : this.validEtudeForm = false)
        this.userForm.statusChanges.subscribe(isValid => isValid === 'VALID' ? this.validUserForm = true : this.validUserForm = false)
        this.facturationForm.statusChanges.subscribe(isValid => isValid === 'VALID' ? this.validFacturationForm = true : this.validFacturationForm = false)
        this.diversForm.statusChanges.subscribe((isValid) => { (isValid === 'VALID' && this.diversForm.get('acceptation_cgu')?.value === true) ? this.validDiversForm = true : this.validDiversForm = false })
        this.registerForm.statusChanges.subscribe(isValid => isValid === 'VALID' ? this.validRegisterForm = true : this.validRegisterForm = false)

        // this.diversForm.get('logiciel_utilise')?.valueChanges.pipe(map((v) => v == 0 ? this.showLogicielPrecisionAutre = true : this.showLogicielPrecisionAutre = false)).subscribe()
        // this.diversForm.get('comment_connaitre_NRU')?.valueChanges.pipe(map((v) => v == 0 ? this.showConnaitrePrecisionAutre = true : this.showConnaitrePrecisionAutre = false)).subscribe()
    }

    /**
     * Passe à l'étape suivante
     *
     * @param event - Evenement de changement d'étape
     */
    onStepChange(event: any) {
        // console.log(event)
        this.stepChangeSubject.next((event.selectedIndex + 1))
    }

    // Envoi le formulaire d'inscription
    sendRegisterForm() {
        console.log(this.registerForm)
        this.registerService.sendRegisterForm(this.registerForm)
    }

    log() {
        //si au moin 5 caractères faire la requete de verif de doublon
        console.log(this.registerForm)
    }

    // Récupère le numéro de Siren
    getSiren() {
        // Retire les espaces
        let formatedNumeroSiren = this.numero_siren.value.replaceAll(' ', '')

        // Si le numéro possède 9 chiffres envoi le numéro
        if (formatedNumeroSiren.length == 9) {
            this.registerService.getSiren(this.numero_siren.value).subscribe({
                next: (r: any) => { // Récupère les informations concernant l'étude
                    const resultat = r.results[0]
                    // console.log(resultat)
                    if (resultat == null || resultat == undefined) {
                        this.numero_siren.setErrors({ siren: 'aucune entreprise' })
                    }
                    // console.log(this.etudeForm)
                    let adresse = (resultat.siege.numero_voie !== null ? resultat.siege.numero_voie + ' ' : '') + (resultat.siege.type_voie !== null ? resultat.siege.type_voie + ' ' : '') + resultat.siege.libelle_voie;
                    this.etudeForm.get('nom_etude')?.patchValue(resultat.nom_complet)
                    this.etudeForm.get('adresse_etude')?.patchValue(adresse)
                    this.etudeForm.get('cp_etude')?.patchValue(resultat.siege.code_postal)
                    this.etudeForm.get('ville_etude')?.patchValue(resultat.siege.libelle_commune)
                    if (resultat.nature_juridique !== '') {
                        const fj = this.listeFormesJuridiques.find((r) => r.code == resultat.nature_juridique)
                        this.etudeForm.get('forme_juridique')?.patchValue(fj?.designation)
                    }
                },
                error: (err: Error) => {
                    console.log(err)
                }
            })
        } else { // Sinon affiche un message d'erreur
            this.numero_siren.setErrors({ siren_nb_caracteres: 'nombre de caractères invalide' })
        }
    }

    /**
     * Défini les jours d'ouvertures à null
     *
     * @param dayOpen - Jour ouvert
     * @param dayClose - Jour fermé
     */
    setHorairesToNull(dayOpen: string, dayClose: string) {
        this.horairesForm.get(dayOpen)?.patchValue(null)
        this.horairesForm.get(dayClose)?.patchValue(null)
    }

    // Définition des conditions de validation du form de facturation
    setFacturationDiffrenteValidators() {
        this.facturationForm.get('adresse_facturation')!.addValidators([Validators.required]);
        this.facturationForm.get('cp_facturation')!.addValidators([Validators.required]);
        this.facturationForm.get('ville_facturation')!.addValidators([Validators.required]);

        this.facturationForm.get('adresse_facturation')!.updateValueAndValidity();
        this.facturationForm.get('cp_facturation')!.updateValueAndValidity();
        this.facturationForm.get('ville_facturation')!.updateValueAndValidity();
    }

    // Retire les conditions de validation du form de facturartion
    clearFacturationDiffrenteValidators() {
        this.facturationForm.get('adresse_facturation')!.clearValidators();
        this.facturationForm.get('cp_facturation')!.clearValidators();
        this.facturationForm.get('ville_facturation')!.clearValidators();

        this.facturationForm.get('adresse_facturation')!.updateValueAndValidity();
        this.facturationForm.get('cp_facturation')!.updateValueAndValidity();
        this.facturationForm.get('ville_facturation')!.updateValueAndValidity();
    }

    genereFakeInscription() {
        this.registerService.fake()
    }

    /**
     * Récupère les messages d'erreurs correspondant aux différents controles
     *
     * @param ctrl - Controle
     * @returns - Message d'erreur
     */
    getFormControlErrorText(ctrl: AbstractControl) {
        if (ctrl.hasError('required')) {
            return 'ce champ est requis';
        } else if (ctrl.hasError('siren')) {
            return 'Aucune entreprise ayant ce siren retrouvée'
        } else if (ctrl.hasError('siren_nb_caracteres')) {
            return 'Nombre invalide de caractères pour le siren'
        } else if (ctrl.hasError('email')) {
            return 'Merci d\'entrer une adresse mail valide';
        } else if (ctrl.hasError('minlength')) {
            return 'le numéro de téléphone est trop court';
        } else if (ctrl.hasError('maxlength')) {
            return 'le numéro de téléphone est trop long';
        } else {
            return 'Ce champ contient une erreur';
        }
    }

    rechercheVille() {
        var cp = this.etudeForm.get('cp_etude')?.value
        if(cp.lenght == 5) {
            this.registerService.rechercheApiVille(cp).subscribe({
                next: (ville : villeFromApi[]) => {
                    if (ville.length == 1) {
                        this.etudeForm.get('ville_etude')?.patchValue(ville[0].nom)
                    }
                    this.listeVilles = ville
                }, error: (e: Error) => {
                    console.log(e.message, e.cause);
                }
            })
        }
    }
}
