import {HttpClient, HttpErrorResponse} from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { environment } from '../../../environment/environment';
import { Booking } from '../../interface/booking.interface';
import {ToolsService} from "../../shared/services/tools.service";

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

    constructor(
        private http: HttpClient,
        private toolsService: ToolsService,
    ) { }

    getAllFuturBooking(forceReload: boolean = false) {
        if (Date.now() - this.listAllFuturBookingLoaded <= 1200000 && !forceReload) {
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
        if (Date.now() - this.listAllPastBookingLoaded <= 1200000 && !forceReload) {
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

    deleteBooking(idBooking: number): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            this.http.post<{ data: any }>(environment.apiURL + '/api/delete-booking', {id: idBooking}).subscribe({
                next: (data: any) => {
                    this.toolsService.openSnackBar('success', data.message);
                    resolve();
                },
                error: (error: HttpErrorResponse) => {
                    this.toolsService.openSnackBar('error', error.error.message);
                    reject(error);
                }
            });
        })
    }

    deleteMultipleBookings(bookingIds: number[]): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            this.http.post<{ data: any }>(environment.apiURL + '/api/delete-multiple-bookings', {
                listIdBooking: bookingIds
            }).subscribe({
                next: (data: any) => {
                    this.toolsService.openSnackBar('success', `${bookingIds.length} réservation(s) supprimée(s)`);
                    resolve();
                },
                error: (error: HttpErrorResponse) => {
                    this.toolsService.openSnackBar('error', error.error.message);
                    reject(error);
                }
            });
        });
    }

}
