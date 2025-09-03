import { Component } from '@angular/core';
import {ICellRendererAngularComp} from "ag-grid-angular";
import {CustomCellRendererInterface} from "../../interfaces/custom-cell-renderer-interface";
import { User } from '../../../interface/user.interface';
import { PopupUpdateUserComponent } from '../../../my-account/gestion-user/popup-update-user/popup-update-user.component';
import {MatDialog} from "@angular/material/dialog";

@Component({
    selector: 'app-cell-action-user-active',
    standalone: true,
    imports: [],
    templateUrl: './cell-action-user-active.component.html',
    styleUrl: './cell-action-user-active.component.sass'
})
export class CellActionUserActiveComponent implements ICellRendererAngularComp {

    user!: User 
    user_status!: string
    componentParent: any
    params: any

    constructor(
        private dialog: MatDialog,
    ) {}

    agInit(params: CustomCellRendererInterface) {
        console.log(params.data)
        this.user = params.data.user
        this.user_status = this.user.valid ? 'Activé' : 'Désactivé'
        this.componentParent = params.context.componentParent
        this.params = params.data
    }

    onUserStatusChange(event: Event, user: User): void {
        const checkbox = event.target as HTMLInputElement;
        const newStatus = checkbox.checked;
        this.componentParent.updateUserStatus(user, newStatus);
    }

    openEditDialog(user: User) {
        this.dialog.open(PopupUpdateUserComponent, {
            data: user,
            disableClose: true,
            maxWidth: '750px',
        });
    }

    refresh(params: any): boolean {
        this.user = params.value;
        return true
    }
}
