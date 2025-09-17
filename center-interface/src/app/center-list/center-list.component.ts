import { Component } from '@angular/core';
import { Center } from '../interface/center.interface';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { BehaviorSubject } from 'rxjs';
import { CenterListService } from './services/center-list.service';
import { localeTextFr } from '../utils/translation/localTextFr';
import { ColDef, GridApi, GridOptions, GridReadyEvent, RowDoubleClickedEvent } from 'ag-grid-community';
import { AgGridAngular } from 'ag-grid-angular';
import { CenterFormComponent } from "../center-form/center-form.component";
import { CenterCalendarComponent } from '../center-calendar/center-calendar.component';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { CommonModule } from '@angular/common';
import { CellActionCenterComponent } from '../shared/custom-cell/cell-action-center/cell-action-center.component';
import { CenterCreateComponent } from '../center-form/center-create/center-create.component';

@Component({
    selector: 'app-center-list',
    standalone: true,
    imports: [
        AgGridAngular,
        ReactiveFormsModule,
        CenterFormComponent,
        CenterCalendarComponent,
        CenterCreateComponent,
        MatProgressSpinnerModule,
        CommonModule
    ],
    templateUrl: './center-list.component.html',
    styleUrl: './center-list.component.sass'
})
export class CenterListComponent {

    centerSelect!: Center
    searchCtrl: FormControl = new FormControl('')
    loaderAgGrid$: BehaviorSubject<boolean> = this.centerListService.loaderAgGrid$
    switchView: 'centerList' | 'centerForm' | 'centerCalendar' | 'centerCreate' = 'centerList'

    rowData!: any[]
    localeTextFr = localeTextFr
    gridCenterList!: GridApi
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
        { field: "name", headerName: "Nom du centre", lockPosition: "left",width: 150 },
        { field: "city", headerName: "Ville", lockPosition: "left",width: 75 },
        { field: "zipcode", headerName: "Code Postal", lockPosition: "left",width: 50 },
        { field: "bande", headerName: "Groupe", lockPosition: "left",width: 75 },
        { field: "action", headerName: "Action", lockPosition: "left", cellClass: 'justify-content-center', width: 120,
            cellRenderer: CellActionCenterComponent
        },
    ]

    constructor(
        private centerListService: CenterListService,
    ) { }

    ngOnInit(): void {
        this.centerListService.getListCenters();
            this.centerListService.listAllCenter$.subscribe(allCenters => {
                this.setRowData(allCenters)
            }) 
    }

    setRowData(center: Center[]) {
        this.rowData = []
        for (const value of center) {
            this.rowData.push({
                id: value.id,
                name: value.name,
                city: value.city,
                zipcode: value.zipcode,
                bande: value.band,
                active: value.active,
                allDataCenter: value,   
                isLoading: false 
            })
        }
    }

    onGridReady(params: GridReadyEvent) {
        this.gridCenterList = params.api;
    }

    // Redimensionne les colonnes du tableau une fois la data 
    // chargé pour qu'elles s'adaptent à la taille de la fenêtre
    firstDataRendered(params: any) {
        params.api.sizeColumnsToFit();
        this.loaderAgGrid$.next(false)
    }

    // Search bar
    onFilterTextBoxChanged(event: any) {
        this.gridCenterList.setGridOption('quickFilterText', this.searchCtrl.value);
    }

    rowDoubleClicked(event: RowDoubleClickedEvent) {
        this.gridCenterList = event.data
        this.centerSelect = event.data.allDataCenter
        this.changeView('centerForm')
    }

    onUpdateComplete(centerUpdated: Center) {
        console.log('IN CENTER-LIST OUTPUT', centerUpdated)
        this.centerListService.updateCenterInList(centerUpdated);
    }

    newCenter() {
        this.switchView = 'centerCreate'
    }

    changeView(viewToDisplay: 'centerList' | 'centerForm' | 'centerCalendar') {
        this.switchView = viewToDisplay
    }

    formatageDate(dateString: Date): string {
        const date = new Date(dateString);
        const options: Intl.DateTimeFormatOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        };
        return new Intl.DateTimeFormat('fr-FR', options).format(date);
    }

    toTitleCase(value: string): string {
        return value.replace(/\w\S*/g, (txt) => {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }

    updateCenterStatus(center: Center, newStatus: boolean) {     
        this.centerListService.updateCenterStatus(center, newStatus)
    }

}
