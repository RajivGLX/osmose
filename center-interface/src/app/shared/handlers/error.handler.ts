import {AbstractControl, FormArray, FormControl, FormGroup, ValidationErrors} from '@angular/forms';
import {Injectable} from '@angular/core';


@Injectable({
    providedIn: 'root'
})
export class ErrorHandler {

    private tabErreurs!: { [key: string]: string };
    private message!: string;


    private static hasError(control: AbstractControl): boolean {
        return control.invalid && (control.dirty || control.touched);
    }

    public handleErrors(form: FormGroup, tabErreurs: any) {
        this.tabErreurs = tabErreurs;
        form.valueChanges.subscribe(() => {
            if (form.invalid) {
                this.findErrorsInForm(form.controls);
            }

        });
    }


    private findErrorsInForm(mainFormAVerif: { [key: string]: AbstractControl }) {

        Object.keys(mainFormAVerif).forEach((champ: string) => {
            if (mainFormAVerif[champ] instanceof FormGroup) {
                // a voir si problème car fonction recursive
                const secondaryForm: FormGroup = mainFormAVerif[champ] as FormGroup;
                this.findErrorsOnForm(secondaryForm, champ);
                this.findErrorsInForm(secondaryForm.controls);
            } else if (mainFormAVerif[champ] instanceof FormArray) {
                const formArray = mainFormAVerif[champ] as FormArray;
                formArray.controls.forEach((control) => {
                    if (control instanceof FormGroup) {
                        this.findErrorsInForm(control.controls);
                    }
                });
            } else if (mainFormAVerif[champ] instanceof FormControl) {

                this.findErrorInFormControl(mainFormAVerif, champ);
            }
        })
    }

    private findErrorsOnForm(formAVerif: FormGroup, champ: string) {
        if (formAVerif.errors) {
            this.getFormControlErrorText(formAVerif.errors, champ);
        }
    }


    private findErrorInFormControl(formulaireAVerif: { [key: string]: AbstractControl }, nomChamp: string) {

        if (ErrorHandler.hasError(formulaireAVerif[nomChamp])) {
            this.getFormControlErrorText(formulaireAVerif[nomChamp].errors as ValidationErrors, nomChamp);
        }
    }

    private getFormControlErrorText(errors: ValidationErrors, champ: string) {

        if (errors['required']) {
            this.message = 'Ce champ est requis';
        } else if (errors['email']) {
            this.message = 'Adresse mail invalide';
        } else if (errors['confirmEqual'] && (champ === 'email' || champ == 'confirm_email')) {
            this.message = 'Les adresses mail  doivent être identiques';
        }else if (errors['confirmEqual'] && (champ === 'password' || champ === 'confirm_password') ) {
            this.message = 'Les mots de passes doivent être identiques';
        } else if (errors['pattern']) {
            this.message = 'Le format est invalide';
        }
        // min et max pour les valeurs numériques
        else if (errors['min']) {
            this.message = 'Le format est trop court';
        } else if (errors['max']) {
            this.message = 'Le format est trop long';
        }
        // maxLength et minLength pour la longueur d'un string
        else if (errors['minlength']) {
            this.message = `Le format attendu est de minimum ${errors['minlength'].requiredLength} caracteres`;
        } else if (errors['maxlength']) {
            this.message = `Le format attendu est de minimum ${errors['maxlength'].requiredLength} caracteres`;
        } else {
            this.message = '';
        }
        this.tabErreurs[champ] = this.message;
        console.log(this.tabErreurs);

        // TO DO
        // } else if (ctrl.hasError('siren')) {
        //      this.message = 'Aucune entreprise ayant ce siren retrouvée'
        // } else if (ctrl.hasError('siren_nb_caracteres')) {
        //      this.message = 'Nombre invalide de caractères pour le siren'

    }

}
