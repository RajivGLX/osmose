import { Component } from '@angular/core'
import { MatProgressSpinner } from '@angular/material/progress-spinner';
import { ICellRendererAngularComp } from 'ag-grid-angular'

@Component({
    standalone: true,
    selector: 'app-cell-action',
    templateUrl: './cell-action-booking.component.html',
    imports: [MatProgressSpinner],

})

export class CellActionBookingComponent implements ICellRendererAngularComp {
    params: any
    componentParent: any

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
}
