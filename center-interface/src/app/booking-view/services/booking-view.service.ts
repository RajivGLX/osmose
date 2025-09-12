import { Injectable } from '@angular/core';
import { Status } from '../../interface/status.interface';
import { environment } from '../../../environment/environment';
import { BehaviorSubject } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { FormGroup } from '@angular/forms';
import { Booking } from '../../interface/booking.interface';
import { ToolsService } from '../../shared/services/tools.service';

@Injectable({
    providedIn: 'root'
})
export class BookingViewService {

    private _status$ = new BehaviorSubject<Status[]>([])
        get status$() {
            return this._status$.asObservable()
        }

    private _allBookingsByPatient$ = new BehaviorSubject<Booking[]>([])
        get allBookingsByPatient$() {
            return this._allBookingsByPatient$.asObservable()
        }
    
    private _bookingByPatient$ = new BehaviorSubject<Booking>({} as Booking)
        get bookingByPatient$() {
            return this._bookingByPatient$.asObservable()
        }    

    private _loaderOneStatus$ = new BehaviorSubject<boolean>(false)
        get loaderOneStatus$() {
            return this._loaderOneStatus$.asObservable()
        } 
    
    public loaderAllStatus$ = new BehaviorSubject<boolean>(false)   

    public listBookingsByPatientLoaded$ = new BehaviorSubject<number>(0)
    public listStatusLoaded = 0

    constructor(
        private http: HttpClient,
        private toolsService: ToolsService
    ) { }

    getStatuses(forceReload: boolean = false) {
        if (Date.now() - this.listStatusLoaded <= 1200000 && forceReload == false) {
            return
        }

        this.http.get<Status[]>(environment.apiURL + '/api/get-status-admin').subscribe({
            next: (v: Status[]) => {
                this._status$.next(v)
                this.listStatusLoaded = Date.now()
                console.log('getStatus :',v)
            },
            error: (e: Error) => {
                console.log(e.message)
            }
        })
    }

    addNewStatus(statusForm: FormGroup) {
        this._loaderOneStatus$.next(true)
        this.http.post<Booking>(environment.apiURL + '/api/add-status-admin', statusForm).subscribe({
            next: (booking: Booking) => {
                this.toolsService.openSnackBar('success','Le statut a bien été ajouté')
                this._bookingByPatient$.next(booking)
                this._loaderOneStatus$.next(false)
            },
            error: () => {
                this.toolsService.openSnackBar('error','Une erreur est survenue lors de l\'ajout du statut')
                this._loaderOneStatus$.next(false)
            }
        })
    }

    addMultipleNewStatus(statusForm: FormGroup) {
        console.log('statusForm :',statusForm.value)
        this.http.post<Booking[]>(environment.apiURL + '/api/add-multiple-status-admin', statusForm.value).subscribe({
            next: (booking: Booking[]) => {
                console.log('return :',booking)
                this.toolsService.openSnackBar('success','Les statuts ont bien été ajouter')
                this._allBookingsByPatient$.next(booking)
                this.loaderAllStatus$.next(false)
            },
            error: (e: Error) => {
                console.log('error', e.message)
                this.toolsService.openSnackBar('error','Une erreur est survenue lors de l\'ajout des statuts')
                this.loaderAllStatus$.next(false)
            }
        })
    }

    getAllBookingsByPatientId(patientId: number, forceReload: boolean = false) {
        this.listBookingsByPatientLoaded$.subscribe({
            next: (value: number) => {
                if (Date.now() - value <= 1200000 && forceReload == false) {
                    return
                }
            }
        })
        
        this.http.get<Booking[]>(environment.apiURL + '/api/get-booking-by-patient/' + patientId).subscribe({
            next: (booking: Booking[]) => {
                this._allBookingsByPatient$.next(booking)
                this.listBookingsByPatientLoaded$.next(Date.now())
            },
            error: (e: Error) => {
                console.log(e.message)
            }
        })
    }
}
