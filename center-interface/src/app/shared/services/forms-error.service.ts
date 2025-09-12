// front/src/app/shared/services/form-error.service.ts
import {Injectable} from '@angular/core';
import {AbstractControl, FormArray, FormGroup, ValidationErrors} from '@angular/forms';
import {Subject} from "rxjs";

@Injectable({
    providedIn: 'root'
})
export class FormErrorService {
    // Sujet pour déclencher les mises à jour forcées
    forceUpdateSubject = new Subject<string | null>();

    // Dictionnaire de messages d'erreur
    private errorMessages: { [key: string]: string } = {
        required: 'Ce champ est requis',
        email: 'L\'adresse email est invalide',
        minlength: 'Ce champ est trop court',
        maxlength: 'Ce champ est trop long',
        pattern: 'Format invalide',
        mismatch: 'Les valeurs ne correspondent pas',
        min: 'Valeur trop petite',
        max: 'Valeur trop grande',
        password: 'Le mot de passe doit contenir au moins 8 caractères avec majuscules, minuscules et chiffres',
        invalidBirthDate: 'L\'utilisateur doit avoir au moins 18 ans',
    };

    // Messages spécifiques à certains champs
    private fieldSpecificMessages: { [field: string]: { [key: string]: string } } = {
        confirm_email: {
            mismatch: 'Les adresses email ne correspondent pas'
        },
        confirm_password: {
            mismatch: 'Les mots de passe ne correspondent pas'
        }
    };

    constructor() {
    }

    // Récupère les erreurs d'un formulaire
    // getFormErrors(form: FormGroup): { [key: string]: any } {
    //     const errors: { [key: string]: any } = {};
    //     Object.keys(form.controls).forEach(controlName => {
    //         const control = form.get(controlName);
    //         if (control && control.errors && (control.touched || control.dirty)) {
    //             errors[controlName] = control.errors;
    //         }
    //     });
    //     return errors;
    // }
    getFormErrors(form: FormGroup): { [key: string]: any } {
        const errors: { [key: string]: any } = {};

        // Fonction récursive pour traiter les contrôles et sous-formulaires
        const processControls = (controls: { [key: string]: AbstractControl }, prefix: string = '') => {
            Object.keys(controls).forEach(controlName => {
                const fullPath = prefix ? `${prefix}.${controlName}` : controlName;
                const control = controls[controlName];

                if (control instanceof FormGroup) {
                    // Traiter le sous-groupe
                    if (control.errors) {
                        errors[fullPath] = control.errors;
                    }
                    processControls((control as FormGroup).controls, fullPath);
                } else if (control && control.errors && (control.touched || control.dirty)) {
                    errors[fullPath] = control.errors;
                }
            });
        };

        processControls(form.controls);
        return errors;
    }


    // Convertit les erreurs en messages lisibles
    getErrorMessage(fieldName: string, errors: ValidationErrors): string {
        if (!errors) return '';

        // Vérifie s'il y a un message spécifique pour ce champ/erreur
        if (this.fieldSpecificMessages[fieldName]) {
            for (const errorType in errors) {
                if (this.fieldSpecificMessages[fieldName][errorType]) {
                    return this.fieldSpecificMessages[fieldName][errorType];
                }
            }
        }

        // Sinon, utilise les messages génériques
        for (const errorType in errors) {
            if (this.errorMessages[errorType]) {
                // Gestion des erreurs avec valeurs dynamiques
                if (errorType === 'minlength') {
                    return `Ce champ doit contenir au moins ${errors['minlength'].requiredLength} caractères`;
                }
                if (errorType === 'maxlength') {
                    return `Ce champ ne doit pas dépasser ${errors['maxlength'].requiredLength} caractères`;
                }
                return this.errorMessages[errorType];
            }
        }

        return 'Erreur de validation';
    }

    // Vérifie si un contrôle a une erreur spécifique
    hasError(control: AbstractControl, errorType: string): boolean {
        return control && control.errors && control.errors[errorType] && (control.touched || control.dirty);
    }

    // Marque tous les champs comme touchés pour afficher les erreurs
    markFormGroupTouched(formGroup: FormGroup) {
        Object.values(formGroup.controls).forEach(control => {
            control.markAsTouched();
            if (control instanceof FormGroup) {
                this.markFormGroupTouched(control);
            } else if (control instanceof FormArray) {
                for (let i = 0; i < control.length; i++) {
                    if (control.at(i) instanceof FormGroup) {
                        this.markFormGroupTouched(control.at(i) as FormGroup);
                    }
                }
            }
        });
    }

    // Marque tous les champs comme touchés et force la mise à jour des erreurs
    markFormGroupTouchedAndUpdate(formGroup: FormGroup) {
        this.markFormGroupTouched(formGroup);
        // Force la mise à jour des erreurs pour tous les champs
        this.forceUpdateSubject.next(null);
    }

    // Force la mise à jour d'un champ spécifique
    forceUpdateField(fieldName: string) {
        this.forceUpdateSubject.next(fieldName);
    }
}