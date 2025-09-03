import { Component } from '@angular/core'
import { MatProgressSpinner } from '@angular/material/progress-spinner';
import { ICellRendererAngularComp } from 'ag-grid-angular'
import { User } from '../../../interface/user.interface';

@Component({
    standalone: true,
    selector: 'app-cell-action',
    templateUrl: './cell-action-user.component.html',
    imports: [MatProgressSpinner],

})

export class CellActionUserComponent implements ICellRendererAngularComp {
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
        this.componentParent.userSelect = this.params.data.allDataUser
        this.params.context.componentParent.changeViewForm()
    }

    onStatusChange(event: Event, user: User): void {
        const checkbox = event.target as HTMLInputElement;
        const newStatus = checkbox.checked;
        this.params.data.isLoading = true;
        this.componentParent.updateUserStatus(user, newStatus)
    }
}
