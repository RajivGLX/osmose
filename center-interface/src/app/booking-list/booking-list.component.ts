import { Component, OnInit } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { AgGridAngular } from 'ag-grid-angular';
import { localeTextFr } from '../utils/translation/localTextFr';
import { GridApi } from './../../../node_modules/ag-grid-community/dist/types/core/api/gridApi.d';
import { ColDef } from './../../../node_modules/ag-grid-community/dist/types/core/entities/colDef.d';
import { GridReadyEvent, RowDoubleClickedEvent } from './../../../node_modules/ag-grid-community/dist/types/core/events.d';
import { BookingListService } from './services/booking-list.service';
import { GridOptions } from 'ag-grid-community';
import { Booking } from '../interface/booking.interface';
import { Center } from '../interface/center.interface';
import { LoginService } from '../login/services/login.service';
import { BookingViewComponent } from '../booking-view/booking-view.component';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { CommonModule } from '@angular/common';
import { BehaviorSubject } from 'rxjs';
import { SlotPipe } from '../utils/pipe/slot.pipe';
import { CellActionBookingComponent } from '../shared/custom-cell/cell-action-booking/cell-action-booking.component';
import { FormatageDatePipe } from '../utils/pipe/date-format.pipe';


@Component({
    selector: 'app-booking-list',
    standalone: true,
    imports: [
        AgGridAngular, 
        ReactiveFormsModule, 
        BookingViewComponent,
        MatProgressSpinnerModule, 
        CommonModule
    ],
    templateUrl: './booking-list.component.html',
    styleUrl: './booking-list.component.sass'
})

export class BookingListComponent implements OnInit {

    bookingSelect!: Booking;
    centerSelect!: Center
    searchCtrl: FormControl = new FormControl('')
    loaderAgGrid$: BehaviorSubject<boolean> = this.bookingListService.loaderAgGrid$
    switchView: 'bookingList' | 'bookingView' = 'bookingList'
    currentView: 'futur' | 'past' = 'futur'

    rowData!: any[]
    paginationPageSize = 20;
    localeTextFr = localeTextFr
    gridBookingList!: GridApi;
    defaultColDef: ColDef = {
        sortable: true,
        filter: true,
        resizable: true,
    }
    gridOptions: GridOptions = {
        autoSizeStrategy: {
            type: 'fitGridWidth',
        },
        context: {
            componentParent: this
        }
    }

    colDefs: ColDef[] = [
        { field: "id", headerName: "id", lockPosition: "left", width: 100 },
        { field: "patient", headerName: "Patient", lockPosition: "left",
            cellRenderer: (value: any) => {
                return `<span>${value.value.user.firstname} ${value.value.user.lastname}</span>`
            }
        },
        { field: "dateReserve", headerName: "Date reservation", lockPosition: "left",
            minWidth: 200,
            cellRenderer: (value: any) => {
                sort:'desc'
                return `<span >${value.value} </span>`
            }
        },
        { field: "slotName", headerName: "Créneaux", lockPosition: "left" },
        { field: "reason", headerName: "Raison", lockPosition: "left" },
        {
            field: "lastStatus", headerName: "Statut", lockPosition: "left",
            cellRenderer: (value: any) => {
                return `
                    <span class='booking status'>
                        <span class='pastille'></span>
                        <span class='libelle-status'>${this.toTitleCase(value.value)}</span>
                    </span>
                `
            },
            cellClassRules: {
                'attente': (params) => params.value == 'attente',
                'confirme': (params) => params.value == 'confirmé',
                'contact': (params) => params.value == 'contact',
                'annule': (params) => params.value == 'annulé',
                'refuse': (params) => params.value == 'refusé',
                'depasse': (params) => params.value == 'dépassé',
            },
            headerClass: 'custom-header-last'
        },
        { field: "action", headerName: "Action", lockPosition: "left", cellClass: 'justify-content-center', width: 120,
            cellRenderer: CellActionBookingComponent
        },
    ]

    constructor(
        private bookingListService: BookingListService,
        private loginService: LoginService,
        private slotPipe: SlotPipe,
        private formatageDate: FormatageDatePipe
    ) { }

    ngOnInit(): void {
        this.loginService._userConnected$.subscribe(user => {
            if (user){
                this.centerSelect = user.administrator.centers[0]
            }
        })
        this.loadBookings()
    }

    setRowData(bookings: Booking[]) {
        this.rowData = []
        for (const value of bookings) {
            this.rowData.push({
                id: value.id,
                patient: value.patient,
                center: this.centerSelect,
                slotName: this.slotPipe.transform(value.availability.slot.name),
                availability: value.availability,
                dateReserve: this.formatageDate.transform(value.dateReserve),
                createAt: this.formatageDate.transform(value.createAt),
                comment: value.comment,
                reason: value.reason,
                statusBookings: value.statusBookings,
                lastStatus: value.statusBookings[value.statusBookings.length - 1].status.name,
                allDataBooking: value,
            })
        }
    }

    loadBookings() {
        this.rowData = [];
        if (this.currentView === 'futur') {
            this.bookingListService.getAllFuturBooking();
            this.bookingListService.listAllFuturBookings$.subscribe(allBookings => {
                this.setRowData(allBookings)
            })
        } else {
            this.bookingListService.getAllPastBooking();
            this.bookingListService.listAllPastBookings$.subscribe(allBookings => {
                this.setRowData(allBookings)
            })
        }
    }

    onGridReady(params: GridReadyEvent) {
        this.gridBookingList = params.api;
    }

    onRadioChange(view: 'futur' | 'past') {
        this.currentView = view;
        this.loadBookings();
    }

    // Redimensionne les colonnes du tableau une fois la data 
    // chargé pour qu'elles s'adaptent à la taille de la fenêtre
    firstDataRendered(params: any) {
        params.api.sizeColumnsToFit();
        this.loaderAgGrid$.next(false)
    }

    // Search bar
    onFilterTextBoxChanged(event: any) {
        this.gridBookingList.setGridOption('quickFilterText', this.searchCtrl.value);
    }

    rowDoubleClicked(event: RowDoubleClickedEvent) {
        this.bookingSelect = event.data
        this.changeView('bookingView')
    }

    changeView(viewToDisplay: 'bookingList' | 'bookingView') {
        this.switchView = viewToDisplay
    }

    // Mise à jour l'objet dans la liste des réservations
    onBookingUpdated(updatedBooking: Booking) {
        this.bookingListService.listAllFuturBookings$.subscribe(allBookings => {
            const index = allBookings.findIndex(bookings => bookings.id === updatedBooking.id);
            if (index !== -1) {
                allBookings[index] = updatedBooking;
            }
            this.setRowData(allBookings)
        })
    }

    toTitleCase(value: string): string {
        return value.replace(/\w\S*/g, (txt) => {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }

}
