import {Component, Inject} from '@angular/core';
import {
    MAT_DIALOG_DATA,
    MatDialogActions,
    MatDialogClose,
    MatDialogContent,
    MatDialogRef,
    MatDialogTitle,
} from '@angular/material/dialog';
import {MatButton} from "@angular/material/button";
import {interval, Subscription} from "rxjs";

@Component({
    selector: 'app-confirmation-dialog',
    standalone: true,
    imports: [
        MatDialogContent,
        MatDialogActions,
        MatButton,
        MatDialogClose,
        MatDialogTitle
    ],
    templateUrl: './confirmation-dialog.component.html',
    styleUrl: './confirmation-dialog.component.sass'
})
export class ConfirmationDialogComponent {

    title!: string;
    message!: string;
    underMessage!: string;
    btnOkText!: string;
    btnCancelText!: string;
    countdown: number = 60;
    private countdownSubscription?: Subscription;
    constructor(@Inject(MAT_DIALOG_DATA) public data: any, private dialogRef: MatDialogRef<ConfirmationDialogComponent>) {

        this.title = this.data.title;
        this.message = this.data.message;
        this.underMessage = this.data.underMessage;
        this.btnOkText = this.data.btnOkText;
        this.btnCancelText = this.data.btnCancelText;
        // Récupérer la durée du minuteur si fournie
        if (this.data.countdown) {
            this.countdown = this.data.countdown;
        }
    }

    ngOnInit(): void {
        // Démarrer le minuteur si countdown est activé
        if (this.data.showCountdown) {
            this.startCountdown();
        }
    }

    ngOnDestroy(): void {
        if (this.countdownSubscription) {
            this.countdownSubscription.unsubscribe();
        }
    }

    private startCountdown(): void {
        this.countdownSubscription = interval(1000).subscribe(() => {
            this.countdown--;
            if (this.countdown <= 0) {
                this.dialogRef.close(false); // Fermeture automatique après expiration
            }
        });
    }

    confirm() {
        this.dialogRef.close(true); // Retourne `true` pour la confirmation
    }

    cancel() {
        this.dialogRef.close(false); // Retourne `false` pour l'annulation
    }

}
