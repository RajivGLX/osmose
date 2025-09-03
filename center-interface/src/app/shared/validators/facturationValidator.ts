import { AbstractControl, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';

export function facturationValidator(): ValidatorFn {
    return (formGroup: AbstractControl): ValidationErrors | null => {

        const differentFacturation = formGroup.get('different_facturation')?.value;
        const fields = ['address_facturation', 'city_facturation', 'zipcode_facturation'];

        fields.forEach(field => {
            const control = formGroup.get(field);
            if (differentFacturation) {
                // Ajouter le validateur "required" si facturation_differente est activée
                if (!control?.hasValidator(Validators.required)) {
                    control?.setValidators([Validators.required]);
                    if (field == "zipcode_facturation") {
                        control?.setValidators([Validators.required, Validators.minLength(5), Validators.maxLength(5)]);
                    }
                    console.log('ca active')
                }
            } else {
                // Supprimer le validateur "required" si facturation_differente est désactivée
                if (control?.hasValidator(Validators.required)) {
                    control?.clearValidators();
                    console.log('ca desactive')
                }
            }
            // Met à jour la validité du champ sans émettre d'événement global
            control?.updateValueAndValidity({ onlySelf: true, emitEvent: false });
        });

        return null;
    };
}
