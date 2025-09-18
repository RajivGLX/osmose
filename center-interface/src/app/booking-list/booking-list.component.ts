import { Component, OnInit } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { AgGridAngular } from 'ag-grid-angular';
import { localeTextFr } from '../utils/translation/localTextFr';
import { GridReadyEvent, RowDoubleClickedEvent, ColDef, GridApi } from 'ag-grid-community';
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
import {User} from "../interface/user.interface";
import {MatDialog} from "@angular/material/dialog";
import {ConfirmationDialogComponent} from "../shared/confirmation-dialog/confirmation-dialog.component";


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
    userAdmin!: User
    searchCtrl: FormControl = new FormControl('')
    loaderAgGrid$: BehaviorSubject<boolean> = this.bookingListService.loaderAgGrid$
    switchView: 'bookingList' | 'bookingView' = 'bookingList'
    currentView: 'futur' | 'past' = 'futur'
    selectedBookings: Set<number> = new Set();
    isSelectionMode = false;

    rowData!: any[]
    paginationPageSize = 20;
    localeTextFr = localeTextFr
    gridBookingList!: GridApi;
    defaultColDef: ColDef = {sortable: true, filter: true, resizable: true}
    gridOptions: GridOptions = {
        autoSizeStrategy: {type: 'fitGridWidth',},
        context: {componentParent: this},
        rowSelection: {mode: 'multiRow'},
        onSelectionChanged: () => this.onSelectionChanged()
    }

    colDefs: ColDef[] = [
        { field: "id", headerName: "id", width: 70 },
        { field: "patient", headerName: "Patient"},
        { field: "dateReserve", headerName: "Date reservation"},
        { field: "slotName", headerName: "Créneaux" },
        { field: "reason", headerName: "Raison" },
        {
            field: "lastStatus", headerName: "Statut",
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
        { field: "action", headerName: "Action", cellClass: 'justify-content-center', width: 120,
            cellRenderer: CellActionBookingComponent
        },
    ]

    constructor(
        private bookingListService: BookingListService,
        private loginService: LoginService,
        private slotPipe: SlotPipe,
        private formatageDate: FormatageDatePipe,
        private dialog: MatDialog,
    ) { }

    ngOnInit(): void {
        this.loginService._userConnected$.subscribe(user => {
            if (user && !user.adminOsmose) {
                this.centerSelect = user.administrator.centers[0]
                this.userAdmin = user
            }else if (user && user.adminOsmose) {
                this.userAdmin = user
                this.colDefs = this.colDefs.filter(col => col.field !== "reason");
            }
        })
        this.loadBookings()
    }

    setRowData(bookings: Booking[]) {
        this.rowData = []
        for (const value of bookings) {
            this.rowData.push({
                id: value.id,
                patient: value.patient.user.firstname + ' ' + value.patient.user.lastname,
                center: this.centerSelect,
                slotName: this.slotPipe.transform(value.availability.slot.name),
                availability: value.availability,
                dateReserve: this.formatageDate.transform(value.dateReserve),
                createAt: this.formatageDate.transform(value.createAt),
                comment: value.comment,
                reason: value.reason,
                statusBookings: value.statusBookings,
                lastStatus: value.statusBookings[value.statusBookings.length - 1].status.name,
                userAdmin: this.userAdmin,
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

    onSelectionChanged(): void {
        if (!this.gridBookingList) return;

        const selectedRows = this.gridBookingList.getSelectedRows();
        this.selectedBookings = new Set(selectedRows.map(row => row.id));
    }

    deleteSelectedBookings(): void {
        if (this.selectedBookings.size === 0) return;

        this.dialog.open(ConfirmationDialogComponent, {
            data: {
                title: "Suppression multiple de réservations",
                message: `Êtes-vous certain de vouloir supprimer ${this.selectedBookings.size} réservation(s) ?`,
                btnOkText: "Supprimer",
                btnCancelText: "Annuler",
            }
        }).afterClosed().subscribe((confirm: boolean) => {
            if (confirm) {
                const selectedIds = Array.from(this.selectedBookings);
                this.loaderAgGrid$.next(true);
                this.bookingListService.deleteMultipleBookings(selectedIds).then(() => {
                    this.selectedBookings.clear();
                    this.gridBookingList?.deselectAll();
                    this.loadBookingsAfterDelete();
                }).catch(error => {
                    this.loaderAgGrid$.next(false);
                    console.error('Erreur lors de la suppression des réservations:', error);
                });
            }
        });
    }

    deleteBooking(idBooking: number): Promise<void> {
        return this.bookingListService.deleteBooking(idBooking).then(() => {
            // Mettre à jour les données localement
            this.removeBookingFromLocalData(idBooking);
        });
    }

    private removeBookingFromLocalData(deletedId: number): void {
        if (this.currentView === 'futur') {
            const currentBookings = this.bookingListService['_listAllFuturBookings$'].value;
            const updatedBookings = currentBookings.filter(booking => booking.id !== deletedId);
            this.bookingListService['_listAllFuturBookings$'].next(updatedBookings);
        } else {
            const currentBookings = this.bookingListService['_listAllPastBookings$'].value;
            const updatedBookings = currentBookings.filter(booking => booking.id !== deletedId);
            this.bookingListService['_listAllPastBookings$'].next(updatedBookings);
        }
    }

    private loadBookingsAfterDelete(): void {
        if (this.currentView === 'futur') {
            this.bookingListService.getAllFuturBooking(true); // forceReload = true
        } else {
            this.bookingListService.getAllPastBooking(true); // forceReload = true
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
        this.bookingSelect = event.data.allDataBooking
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
