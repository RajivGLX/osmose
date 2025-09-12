// front/src/app/shared/directives/form-error.directive.ts
import {Directive, ElementRef, Input, OnDestroy, OnInit, Optional} from '@angular/core';
import {AbstractControl, ControlContainer, NgControl} from '@angular/forms';
import {FormErrorService} from '../services/forms-error.service';
import {Subscription} from 'rxjs';

@Directive({
    selector: '[appFormError]',
    standalone: true
})
export class FormErrorDirective implements OnInit, OnDestroy {
    @Input('appFormError') fieldName!: string;

    private statusChanges!: Subscription;
    private valueChanges!: Subscription;
    private forceUpdateSub!: Subscription;

    constructor(
        private el: ElementRef,
        private formErrorService: FormErrorService,
        @Optional() private controlContainer: ControlContainer,
        @Optional() private controlName: NgControl
    ) {
    }

    ngOnInit(): void {
        const control = this.controlContainer?.control?.get(this.fieldName);

        if (!control) {
            console.error(`Contrôle non trouvé pour le champ: ${this.fieldName}`);
            return;
        }

        // Observer les changements d'état et de valeur
        this.statusChanges = control.statusChanges.subscribe(() => {
            this.updateErrorMessage(control);
        });

        this.valueChanges = control.valueChanges.subscribe(() => {
            this.updateErrorMessage(control);
        });

        // S'abonner aux notifications de mise à jour forcée
        this.forceUpdateSub = this.formErrorService.forceUpdateSubject.subscribe(fieldToUpdate => {
            if (!fieldToUpdate || fieldToUpdate === this.fieldName || fieldToUpdate === this.controlName?.name) {
                this.updateErrorMessage(control);
            }
        });

        // Initialisation
        this.updateErrorMessage(control);
    }

    ngOnDestroy(): void {
        this.statusChanges?.unsubscribe();
        this.valueChanges?.unsubscribe();
        this.forceUpdateSub?.unsubscribe();
    }

    private updateErrorMessage(control: AbstractControl): void {
        if (control.errors && (control.touched || control.dirty)) {
            const errorMessage = this.formErrorService.getErrorMessage(
                String(this.fieldName || this.controlName?.name || ''),
                control.errors
            );
            this.el.nativeElement.textContent = errorMessage;
        } else {
            this.el.nativeElement.textContent = '';
        }
    }
}