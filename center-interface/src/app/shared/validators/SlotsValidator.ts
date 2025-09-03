import { AbstractControl, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';

export function slotsValidator(open: string, close: string, closeSlot: string, partie: string): ValidatorFn {
    return (ctrl: AbstractControl): null | ValidationErrors => {

        const openControl = ctrl.get(open);
        const closeControl = ctrl.get(close);
        const closeSlotControl = ctrl.get(closeSlot);

        if (!openControl || !closeControl || !closeSlotControl) {
            return null;
        }

        if (closeSlotControl.value) {
            // Si closeSlot est true, les champs open et close ne sont pas requis
            if (openControl.hasValidator(Validators.required)) {
                openControl.clearValidators();
                openControl.updateValueAndValidity({ emitEvent: false });
            }
            if (closeControl.hasValidator(Validators.required)) {
                closeControl.clearValidators();
                closeControl.updateValueAndValidity({ emitEvent: false });
            }
        } else {
            // Si closeSlot est false, les champs open et close sont requis
            if (!openControl.hasValidator(Validators.required)) {
                openControl.setValidators([Validators.required]);
                openControl.updateValueAndValidity({ emitEvent: false });
            }
            if (!closeControl.hasValidator(Validators.required)) {
                closeControl.setValidators([Validators.required]);
                closeControl.updateValueAndValidity({ emitEvent: false });
            }
        }

        return null;
    };
}