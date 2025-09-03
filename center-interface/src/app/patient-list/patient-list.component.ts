import { Component } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { AgGridAngular } from 'ag-grid-angular';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { CommonModule } from '@angular/common';
import { User } from '../interface/user.interface';
import { BehaviorSubject } from 'rxjs';
import { PatientFormComponent } from '../patient-form/patient-form.component';
import { ColDef, GridApi, GridOptions, GridReadyEvent, RowDoubleClickedEvent } from 'ag-grid-community';
import { localeTextFr } from '../utils/translation/localTextFr';
import { CellActionUserComponent } from '../shared/custom-cell/cell-action-user/cell-action-user.component';
import { RolePipe } from '../utils/pipe/role.pipe';
import { LoginService } from '../login/services/login.service';
import { PatientFormService } from '../patient-form/services/patient-form.service';
import { Patient } from '../interface/patient.interface';
import { PatientListService } from './services/patient-list.service';


@Component({
    selector: 'app-patient-list',
    standalone: true,
    imports: [
        AgGridAngular,
        ReactiveFormsModule,
        PatientFormComponent,
        MatProgressSpinnerModule,
        CommonModule,
        RolePipe
    ],
    providers: [RolePipe], // Ajouter RolePipe aux providers
    templateUrl: './patient-list.component.html',
    styleUrl: './patient-list.component.sass'
})
export class PatientListComponent {
    userSelect!: User
    currentAdmin!: User
    searchCtrl: FormControl = new FormControl('')
    loaderAgGrid$: BehaviorSubject<boolean> = this.patientListService.loaderAgGrid$
    switchView: 'patientList' | 'patientForm' | 'patientCreate' = 'patientList'

    rowData!: any[]
    localeTextFr = localeTextFr
    gridPatientList!: GridApi
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
        private patientListService: PatientListService,
        private loginService: LoginService,
        private rolePipe: RolePipe,
    ) { }

    ngOnInit(): void {
        this.loginService._userConnected$.subscribe( user => {
            if(user){
                this.currentAdmin = user
                this.patientListService.getAllPatients();
                if(this.currentAdmin.adminDialyzone) {
                    this.patientListService.listAllPatients$.subscribe(allPatients => {
                        this.setRowData(allPatients)
                    }) 
                }else {
                    
                }
            }
        })
        
    }

    setRowData(allPatients: User[]) {
        this.rowData = []
        for (const value of allPatients) {
            this.rowData.push({
                id: value.id,
                name: `${value.firstname} ${value.lastname}`,   
                email: value.email,
                valid: value.valid,
                role: this.rolePipe.transform(value.roles[0]),
                allDataUser: value, 
                isLoading: false 
            })
        }
    }

    onGridReady(params: GridReadyEvent) {
        this.gridPatientList = params.api;
    }

    // Redimensionne les colonnes du tableau une fois la data 
    // chargé pour qu'elles s'adaptent à la taille de la fenêtre
    firstDataRendered(params: any) {
        params.api.sizeColumnsToFit();
        this.loaderAgGrid$.next(false)
    }

    // Search bar
    onFilterTextBoxChanged(event: any) {
        this.gridPatientList.setGridOption('quickFilterText', this.searchCtrl.value);
    }

    rowDoubleClicked(event: RowDoubleClickedEvent) {
        console.log('event', event)
        this.gridPatientList = event.data
        this.userSelect = event.data.allDataUser
        this.changeView('patientForm')
    }

    onUpdateComplete(patientUpdated: User) {
        this.patientListService.updatePatientInList(patientUpdated);
    }

    changeView(viewToDisplay: 'patientList' | 'patientForm') {
        this.switchView = viewToDisplay
    }

    newPatient() {
        this.switchView = 'patientCreate'
    }

    changeViewForm() {
        this.switchView = 'patientForm'
    }

    updateUserStatus(user: User, newStatus: boolean) {
        this.patientListService.updateUserStatus(user, newStatus)
    }
}
