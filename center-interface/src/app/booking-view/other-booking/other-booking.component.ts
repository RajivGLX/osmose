import { Component, Input, OnInit } from '@angular/core';
import { Booking } from '../../interface/booking.interface';
import { BookingViewService } from '../services/booking-view.service';
import { localeTextFr } from '../../utils/translation/localTextFr';
import { ColDef, GridApi, GridOptions } from 'ag-grid-community';
import { GridReadyEvent } from './../../../../node_modules/ag-grid-community/dist/types/core/events.d';
import { AgGridAngular } from 'ag-grid-angular';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { Status } from '../../interface/status.interface';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { ReactiveFormsModule, FormArray, FormControl, FormGroup, FormBuilder, Validators } from '@angular/forms';
import { Observable } from 'rxjs';
import { CommonModule } from '@angular/common';
import { ToolsService } from '../../shared/services/tools.service';
import { FormatageDatePipe } from '../../utils/pipe/date-format.pipe';


@Component({
    selector: 'app-other-booking',
    standalone: true,
    imports: [
        AgGridAngular,
        MatInputModule,
        MatSelectModule,
        MatProgressSpinnerModule,
        ReactiveFormsModule,
        CommonModule
    ],
    templateUrl: './other-booking.component.html',
    styleUrl: './other-booking.component.sass'
})
export class OtherBookingComponent implements OnInit {

    @Input() booking!: Booking

    allStatus: Status[] = []
    statusMultipleForm: FormGroup
    loaderAllStatus$: Observable<boolean> = this.bookingViewService.loaderAllStatus$


    rowData!: any[]
    paginationPageSize = 20
    localeTextFr = localeTextFr
    gridBookingList!: GridApi
    defaultColDef: ColDef = {
        sortable: true,
        filter: true,
        resizable: true,
    }
    gridOptions: GridOptions = {
        autoSizeStrategy: {
            type: 'fitGridWidth',
        },
        isRowSelectable: (params) => {
            const status = params.data.lastStatus.toLowerCase()
            return !['confirmé', 'annulé', 'refusé'].includes(status)
        },
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
            }
        },
        {
            headerCheckboxSelection: true,
            checkboxSelection: (params: any) => {
                const status = params.data.lastStatus.toLowerCase()
                return !['confirmé', 'annulé', 'refusé'].includes(status)
            },
            width: 50,
            suppressSizeToFit: true,
            headerClass: 'custom-header-last'
        },
    ]

    constructor(
        private bookingViewService: BookingViewService,
        private fb: FormBuilder,
        private toolsService: ToolsService,
        private formatageDate: FormatageDatePipe
    ) {
        this.statusMultipleForm = this.fb.group({
            idStatus: ['', Validators.required], // Initialisation du champ 'status'
            bookings: [''] // Initialisation du champ 'bookingId'
        })
    }

    ngOnInit(): void {
        
        this.loadAllBookingsByPatient()

        this.bookingViewService.allBookingsByPatient$.subscribe({
            next: (booking: Booking[]) => {
                this.setRowData(booking)
            }
        })
    }

    loadAllBookingsByPatient(forceReload: boolean = false) {
        this.bookingViewService.getAllBookingsByPatientId(this.booking.patient.id, forceReload)
        this.bookingViewService.status$.subscribe({
            next: (value: Status[]) => {
                this.allStatus = value
            }
        })
    }

    onGridReady(params: GridReadyEvent) {
        this.gridBookingList = params.api
    }

    // Redimensionne les colonnes du tableau une fois la data chargé pour qu'elles s'adaptent à la taille de la fenêtre
    firstDataRendered(params: any) {
        params.api.sizeColumnsToFit()
    }

    setRowData(bookings: Booking[]) {
        this.rowData = []
        for (const value of bookings) {
            this.rowData.push({
                id: value.id,
                patient: value.patient,
                slotName: value.availability.slot.name,
                availability: value.availability,
                dateReserve: this.formatageDate.transform(value.dateReserve),
                createAt: this.formatageDate.transform(value.createAt),
                comment: value.comment,
                reason: value.reason,
                statusBookings: value.statusBookings,
                lastStatus: value.statusBookings[value.statusBookings.length - 1].status.name
            })
        }
    }

    updateSelectedBookingsStatus() {
        if (this.statusMultipleForm.invalid) {
            return
        }
        const selectedNodes = this.gridBookingList.getSelectedNodes()
        if (selectedNodes.length === 0) {
            this.toolsService.openSnackBar('Veuillez sélectionner au moins une réservation', true)
            return
        }
        const arrayIdBooking = selectedNodes.map((item: any) => item.data.id)
        this.statusMultipleForm.patchValue({ bookings: arrayIdBooking })
        this.bookingViewService.addMultipleNewStatus(this.statusMultipleForm)
        this.loadAllBookingsByPatient(true)

    }

    checkIfStatusIsUpdatable(booking: Booking): boolean {
        const statusActive = booking.statusBookings.find(item => item.status_active === true)
        console.log('statusActive :',statusActive)
        if (statusActive && (statusActive.status.name === 'confirmé' || statusActive.status.name === 'annulé' || statusActive.status.name === 'refusé')) {
            return false
        }
        return true
    }

    toTitleCase(value: string): string {
        return value.replace(/\w\S*/g, (txt) => {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()
        })
    }

}
