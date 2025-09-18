import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { Booking } from '../interface/booking.interface';
import { Status } from '../interface/status.interface';
import { BookingViewService } from './services/booking-view.service';
import { FormGroup, ReactiveFormsModule } from '@angular/forms';
import {ErrorHandler} from "../shared/handlers/error.handler";
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { OtherBookingComponent } from './other-booking/other-booking.component';
import { BehaviorSubject, Observable } from 'rxjs';
import { LoaderComponent } from '../loader/loader.component';
import { FormatageDatePipe } from '../utils/pipe/date-format.pipe';
import { SlotPipe } from '../utils/pipe/slot.pipe';
import { FolderPatientComponent } from './folder-patient/folder-patient.component';
import {User} from "../interface/user.interface";
import {FormErrorDirective} from "../shared/directives/form-error.directive";
import {FormErrorService} from "../shared/services/forms-error.service";

@Component({
    selector: 'app-booking-view',
    standalone: true,
    imports: [
        CommonModule,
        ReactiveFormsModule,
        MatInputModule,
        MatSelectModule,
        LoaderComponent,
        OtherBookingComponent,
        FolderPatientComponent,
        FormatageDatePipe,
        SlotPipe,
        FormErrorDirective,
    ],
    templateUrl: './booking-view.component.html',
    styleUrl: './booking-view.component.sass'
})

export class BookingViewComponent implements OnInit {

    @Input() booking !: Booking
    @Input() userAdmin !: User
    @Output() closeVueDetaillee = new EventEmitter<boolean>(true)
    @Output() bookingUpdated = new EventEmitter<Booking>();

    ongletToDisplay: number = 1
    allStatus: Status[] = []
    messageErrors : any = {};
    changeOneStatusForm!: FormGroup
    loaderOneStatus: Observable<boolean> = this.bookingViewService.loaderOneStatus$
    statusIsUpdated: BehaviorSubject<boolean> = new BehaviorSubject<boolean>(true)

    statusForm = this.bookingViewService.statusForm
    statusFormReplace = this.bookingViewService.statusFormReplace

    errors: any = {}

    constructor(
        private bookingViewService: BookingViewService,
        private errorHandler : ErrorHandler,
        private formErrorService: FormErrorService,
    ) { 
    }

    ngOnInit(): void {
        this.checkIfStatusIsUpdatable(this.booking)
        this.statusForm.patchValue({ idBooking: this.booking.id });

        if(this.userAdmin.adminOsmose){
            this.statusFormReplace.patchValue({ idBooking: this.booking.id });
            this.errorHandler.handleErrors(this.statusFormReplace, this.messageErrors);
        }

        this.bookingViewService.getStatuses()
        this.bookingViewService.status$.subscribe({
            next: (value: Status[]) => {
                this.allStatus = value
            }
        })
    }

    onSubmitAddStatus(): void {
        if (this.statusForm.valid) {
            this.bookingViewService.addNewStatus(this.statusForm)
            this.refreshBooking()
        }else{
            this.formErrorService.markFormGroupTouchedAndUpdate(this.statusForm);
            this.errors = this.formErrorService.getFormErrors(this.statusForm);
        }
    }

    onSubmitReplaceStatus(): void {
        if (this.statusFormReplace.valid) {
            this.bookingViewService.replaceStatus(this.statusFormReplace).then(() => {
                this.refreshBooking()
            })
        }else{
            this.formErrorService.markFormGroupTouchedAndUpdate(this.statusFormReplace);
            this.errors = this.formErrorService.getFormErrors(this.statusFormReplace);
        }
    }

    refreshBooking(){
        this.bookingViewService.bookingByPatient$.subscribe({
            next: (value: Booking) => {
                this.booking = value
                this.bookingUpdated.emit(value);
                this.statusForm.patchValue({ idStatus: 0 });
                this.statusFormReplace.patchValue({ idStatus: 0 });
                this.checkIfStatusIsUpdatable(value);
            }
        })
    }

    // Change l'onglet à afficher
    switchOnglet(onglet: number) {
        this.ongletToDisplay = onglet
    }

    checkIfStatusIsUpdatable(booking: Booking) {
        if(new Date(booking.availability.date) > new Date()){
            const statusActive = booking.statusBookings.find(item => item.status_active);
            if (statusActive && (statusActive.status.name === 'confirmé' || statusActive.status.name === 'annulé' || statusActive.status.name === 'refusé')
            ) {
                return this.statusIsUpdated.next(false)
            }
        }else{
            return this.statusIsUpdated.next(false)
        }
    }

    // Retourne la liste des statuts disponibles en excluant le statut actuel
    getAvailableStatuses(): Status[] {
        if (this.userAdmin.adminOsmose || !this.booking || !this.allStatus || !this.booking.statusBookings.length) {
            return this.allStatus;
        }
        
        // Récupérer l'ID du statut actuel (celui qui est actif)
        const currentStatusId = this.booking.statusBookings
            .find(statusBooking => statusBooking.status_active)?.status.id;
            
        // Filtrer les statuts pour exclure celui qui est déjà attribué
        return this.allStatus.filter(status => status.id !== currentStatusId);
    }

    // Retourne sur la vue générale (over-view)
    goBack() {
        this.closeVueDetaillee.emit(true)
        this.bookingViewService.listBookingsByPatientLoaded$.next(0)
    }
    
}
