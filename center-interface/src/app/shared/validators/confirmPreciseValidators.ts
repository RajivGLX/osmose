import { AbstractControl, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';

export function confirmPreciseValidators(radio: string, confirm: string): ValidatorFn {
    return (formGroup: AbstractControl): null | ValidationErrors => {
        const mainControl = formGroup.get(radio);
        const confirmControl = formGroup.get(confirm);

        if (!mainControl || !confirmControl) {
            return {
                confirmPrecise: 'invalid control names'
            };
        }

        const mainValue = mainControl.value;
        const confirmValue = confirmControl.value;

        if ((mainValue === true || mainValue == 'Autre') && !confirmValue) {
            if (!confirmControl.hasValidator(Validators.required)) {
                console.log('add validator')
                confirmControl.setValidators(Validators.required)
                confirmControl.updateValueAndValidity()
            }
            return {
                confirmPrecise: {
                    message: 'Ce champ est obligatoire quand "Oui" est sélectionné'
                }
            };
        } else {
            if (confirmControl.hasValidator(Validators.required)) {
                confirmControl.clearValidators()
                confirmControl.updateValueAndValidity()
            }
        }

        return null;
    };
    
}
