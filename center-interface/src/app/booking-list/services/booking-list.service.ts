import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { environment } from '../../../environment/environment';
import { Booking } from '../../interface/booking.interface';

@Injectable({
    providedIn: 'root'
})
export class BookingListService {

    private _listAllFuturBookings$ = new BehaviorSubject<Booking[]>([])
    get listAllFuturBookings$() {
        return this._listAllFuturBookings$.asObservable()
    }
    
    private _listAllPastBookings$ = new BehaviorSubject<Booking[]>([])
    get listAllPastBookings$() {
        return this._listAllPastBookings$.asObservable()
    }
    
    public loaderAgGrid$ = new BehaviorSubject<boolean>(false)
    
    private listAllFuturBookingLoaded = 0
    
    private listAllPastBookingLoaded = 0

    constructor(private http: HttpClient) { }

    getAllFuturBooking(forceReload: boolean = false) {
        if (Date.now() - this.listAllFuturBookingLoaded <= 1200000 && forceReload == false) {
            return
        }
        this.loaderAgGrid$.next(true)
        this.http.get<Booking[]>(environment.apiURL + '/api/get-all-futur-booking').subscribe({
            next: (booking: Booking[]) => {
                console.log('Réservations:', booking)
                this._listAllFuturBookings$.next(booking)
                this.listAllFuturBookingLoaded = Date.now()
                this.loaderAgGrid$.next(false)
            },
            error: (e: Error) => {
                console.log(e.message)
                this.loaderAgGrid$.next(false)
            }
        })
    }

    getAllPastBooking(forceReload: boolean = false) {
        if (Date.now() - this.listAllPastBookingLoaded <= 1200000 && forceReload == false) {
            return
        }
        this.loaderAgGrid$.next(true)
        this.http.get<Booking[]>(environment.apiURL + '/api/get-all-past-booking').subscribe({
            next: (v: Booking[]) => {
                this._listAllPastBookings$.next(v)
                this.listAllPastBookingLoaded = Date.now()
                this.loaderAgGrid$.next(false)
            },
            error: (e: Error) => {
                console.log(e.message)
                this.loaderAgGrid$.next(false)
            }
        })
    }
}
