import {Component, signal, WritableSignal} from '@angular/core'
import { MatProgressSpinner } from '@angular/material/progress-spinner';
import { ICellRendererAngularComp } from 'ag-grid-angular'
import { ConfirmationDialogComponent} from "../../confirmation-dialog/confirmation-dialog.component";
import {MatDialog} from "@angular/material/dialog";

@Component({
    standalone: true,
    selector: 'app-cell-action',
    templateUrl: './cell-action-booking.component.html',
    imports: [MatProgressSpinner],

})

export class CellActionBookingComponent implements ICellRendererAngularComp {
    params: any
    componentParent: any
    isLoading: WritableSignal<boolean> = signal(false);

    constructor(
        private dialog: MatDialog,
    ) {
    }

    agInit(params: any): void {
        this.params = params
        this.componentParent = params.context.componentParent
    }

    refresh(): boolean {
        return false
    }

    onEdit(): void {
        this.componentParent.bookingSelect = this.params.data.allDataBooking
        this.params.context.componentParent.changeView('bookingView')
    }

    openDeleteDialog(idBooking: number) {
        this.dialog.open(ConfirmationDialogComponent, {
            data: {
                title: "Suppression d'une réservation",
                message:
                    "Etes vous certain d'effectuer la suppression la réservation ?",
                btnOkText: "Valider",
                btnCancelText: "Annuler",
            }
        }).afterClosed().subscribe((confirm: boolean) => {
            this.isLoading.set(true);
            if (confirm) {
                this.componentParent.deleteBooking(idBooking).finally(() => {
                    this.isLoading.set(false);
                });
            } else {
                this.isLoading.set(false);
            }
        })
    }
}
