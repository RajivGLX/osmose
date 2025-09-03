import { Component } from '@angular/core'
import { MatProgressSpinner } from '@angular/material/progress-spinner';
import { ICellRendererAngularComp } from 'ag-grid-angular'
import { Center } from '../../../interface/center.interface';

@Component({
    standalone: true,
    selector: 'app-cell-action',
    templateUrl: './cell-action-center.component.html',
    imports: [MatProgressSpinner],

})

export class CellActionCenterComponent implements ICellRendererAngularComp {
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
        this.componentParent.centerSelect = this.params.data.allDataCenter
        this.params.context.componentParent.changeView('centerForm')
    }

    onCalendar(): void {
        this.componentParent.centerSelect = this.params.data.allDataCenter
        this.params.context.componentParent.changeView('centerCalendar')
    }

    onStatusChange(event: Event, center: Center): void {
        const checkbox = event.target as HTMLInputElement;
        const newStatus = checkbox.checked;
        this.params.data.isLoading = true;
        this.componentParent.updateCenterStatus(center, newStatus)
    }
}
