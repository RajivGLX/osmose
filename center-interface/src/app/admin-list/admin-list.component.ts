import { Component } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { BehaviorSubject } from 'rxjs';
import { AdminListService } from './services/admin-list.service';
import { localeTextFr } from '../utils/translation/localTextFr';
import { ColDef, GridApi, GridOptions, GridReadyEvent, RowDoubleClickedEvent } from 'ag-grid-community';
import { AgGridAngular } from 'ag-grid-angular';
import { AdminFormComponent } from "../admin-form/admin-form.component";
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { CommonModule } from '@angular/common';
import { CellActionUserComponent } from '../shared/custom-cell/cell-action-user/cell-action-user.component';
import { User } from '../interface/user.interface';
import { RolePipe } from '../utils/pipe/role.pipe';
import { LoginService } from '../login/services/login.service';

@Component({
    selector: 'app-admin-list',
    standalone: true,
    imports: [
        AgGridAngular,
        ReactiveFormsModule,
        AdminFormComponent,
        MatProgressSpinnerModule,
        CommonModule,
    ],
    providers: [RolePipe],
    templateUrl: './admin-list.component.html',
    styleUrl: './admin-list.component.sass'
})
export class AdminListComponent {
    userSelect!: User
    currentAdmin!: User
    searchCtrl: FormControl = new FormControl('')
    loaderAgGrid$: BehaviorSubject<boolean> = this.adminListService.loaderAgGrid$
    switchView: 'adminList' | 'adminForm' | 'adminCreate' = 'adminList'

    rowData!: any[]
    localeTextFr = localeTextFr
    gridAdminList!: GridApi
    defaultColDef: ColDef = {
        sortable: true,
        filter: true,
        resizable: true,
    }
    gridOptions: GridOptions = {
        autoSizeStrategy: {type: 'fitGridWidth'},
        context: {componentParent: this},
        suppressCellFocus: true,
    }

    colDefs: ColDef[] = [
        { field: "id", headerName: "id", lockPosition: "left",width: 50 },
        { field: "name", headerName: "Prenom Nom", lockPosition: "left"},
        { field: "email", headerName: "Email", lockPosition: "left" },
        { field: "role", headerName: "Role", lockPosition: "left" },
        { field: "action", headerName: "Action", lockPosition: "left", cellClass: 'justify-content-center', width: 120,
            cellRenderer: CellActionUserComponent
        },
    ]

    constructor(
        private adminListService: AdminListService,
        private rolePipe: RolePipe,
        private loginService: LoginService
    ) { }

    ngOnInit(): void {
        this.loginService._userConnected$.subscribe( user => {
            if(user){
                this.currentAdmin = user
                this.adminListService.getAllAdmins();
                if(this.currentAdmin.adminDialyzone) {
                    this.adminListService.listAllAdmin$.subscribe(allAdmins => {
                        this.setRowData(allAdmins)
                    }) 
                }else {
                    
                }
            }
        })
        
    }

    setRowData(allAdmins: User[]) {
        this.rowData = []
        for (const value of allAdmins) {
            if(value.id !== this.currentAdmin.id) {
                this.rowData.push({
                    id: value.id,
                    name: `${value.firstname} ${value.lastname}`,   
                    role: this.rolePipe.transform(value.roles[0]),
                    email: value.email,
                    valid: value.valid,
                    allDataUser: value, 
                    isLoading: false 
                })
            }
            
        }
    }

    onGridReady(params: GridReadyEvent) {
        this.gridAdminList = params.api;
    }

    // Redimensionne les colonnes du tableau une fois la data 
    // chargé pour qu'elles s'adaptent à la taille de la fenêtre
    firstDataRendered(params: any) {
        params.api.sizeColumnsToFit();
        this.loaderAgGrid$.next(false)
    }

    // Search bar
    onFilterTextBoxChanged(event: any) {
        this.gridAdminList.setGridOption('quickFilterText', this.searchCtrl.value);
    }

    rowDoubleClicked(event: RowDoubleClickedEvent) {
        console.log('event', event)
        this.gridAdminList = event.data
        this.userSelect = event.data.allDataUser
        this.changeView('adminForm')
    }

    onUpdateComplete(adminUpdated: User) {
        this.adminListService.updateAdminInList(adminUpdated);
    }

    changeView(viewToDisplay: 'adminList' | 'adminForm') {
        this.switchView = viewToDisplay
    }

    newAdmin() {
        this.switchView = 'adminCreate'
    }

    changeViewForm() {
        this.switchView = 'adminForm'
    }

    updateUserStatus(user: User, newStatus: boolean) {
        this.adminListService.updateUserStatus(user, newStatus)
    }
}
