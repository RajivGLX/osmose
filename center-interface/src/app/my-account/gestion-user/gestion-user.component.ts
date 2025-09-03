import { Component, Input, OnInit } from '@angular/core';
import { Observable } from 'rxjs';
import { User } from '../../interface/user.interface';
import { MatDialog } from '@angular/material/dialog';
import {ColDef, GridApi, GridOptions, GridReadyEvent} from 'ag-grid-community';
import { GestionUserService } from './services/gestion-user.service';
import { CommonModule } from '@angular/common';
import { RolePipe } from '../../utils/pipe/role.pipe';
import { LoaderComponent } from '../../loader/loader.component';
import { CellActionUserActiveComponent } from '../../shared/custom-cell/cell-action-user-active/cell-action-user-active.component';
import { Center } from '../../interface/center.interface';
import { localeTextFr } from '../../utils/translation/localTextFr';
import { AgGridAngular } from 'ag-grid-angular';
import { AdminListService } from '../../admin-list/services/admin-list.service';
import { PopupAddUserComponent } from './popup-add-user/popup-add-user.component';

@Component({
    selector: 'app-gestion-user',
    standalone: true,
    imports: [
        CommonModule, 
        LoaderComponent,
        AgGridAngular
    ],
    providers: [RolePipe],
    templateUrl: './gestion-user.component.html',
    styleUrl: './gestion-user.component.sass'
})
export class GestionUserComponent implements OnInit {
    @Input() user!: Observable<User | null>
    center!: Center[]
    listeUsersParEtude$: Observable<User[]> = this.gestionUserService.listUsersEtude$;
    loading: Observable<boolean> = this.gestionUserService.loading$;
    statusLoading: Observable<boolean> = this.gestionUserService.statusLoading$;

    
    colDefs: ColDef[] = [
        {field: "utilisateur", headerName: "Utilisateur", lockPosition: "left"},
        {field: "mail", headerName: "Mail", lockPosition: "left"},
        {field: "role", headerName: "Rôle", lockPosition: "left"},
        { field: "action", headerName: "Action", lockPosition: "left", cellClass: 'justify-content-center', width: 150,
            cellRenderer: CellActionUserActiveComponent
        }
    ]

    rowData!: any[]
    defaultColDef: ColDef = {sortable: false, filter: false, resizable: false,}
    localeTextFr = localeTextFr
    gridUsers!: GridApi
    gridOptions: GridOptions = {
        autoSizeStrategy: {type: 'fitGridWidth'},
        context: {componentParent: this},
        suppressCellFocus: true,
    }

    constructor(
        private dialog: MatDialog, 
        private gestionUserService: GestionUserService,
        private rolePipe: RolePipe,
        private adminListService : AdminListService, 

    ) {}

    ngOnInit(): void {
        this.user.subscribe(
            user => {
                if (user) this.center = user.administrator.centers
            }
        )
        this.gestionUserService.getListeUsersCenter();
        this.listeUsersParEtude$.subscribe((users) => {
            this.rowData = users.filter(user=> !user.roles.includes('ROLE_SUPER_ADMIN'))
                .map((user) => {
                return {
                    utilisateur: user.firstname + ' ' + user.lastname,
                    fonction: user.roles,
                    mail: user.email,
                    role: this.rolePipe.transform(user.roles[0]),
                    valid: user.valid,
                    isLoading: false,
                    user: user
                }
            })
        })
    }

    openAddUserDialog() {
        this.dialog.open(PopupAddUserComponent, {
            disableClose: true,
            maxWidth: '750px',
            data: {
                centers: this.center 
            }
        });
    }

    onGridReady(params: GridReadyEvent) {
        this.gridUsers = params.api;
    }

    // Redimensionne les colonnes du tableau une fois la data chargé pour qu'elles s'adaptent à la taille de la fenêtre
    firstDataRendered(params: any) {
        params.api.sizeColumnsToFit();
    }

    updateUserStatus(user: User, newStatus: boolean) {
        this.adminListService.updateUserStatus(user, newStatus)
    }

}
