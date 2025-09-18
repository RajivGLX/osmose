import { Injectable } from '@angular/core';
import { Status } from '../../interface/status.interface';
import { environment } from '../../../environment/environment';
import { BehaviorSubject } from 'rxjs';
import {HttpClient, HttpErrorResponse} from '@angular/common/http';
import { FormBuilder, FormGroup, Validators} from '@angular/forms';
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
    public statusForm!: FormGroup
    public statusFormReplace!: FormGroup
    public listStatusLoaded = 0

    constructor(
        private http: HttpClient,
        private fb: FormBuilder,
        private toolsService: ToolsService
    ) {
        this.initializeFormGroup()
    }

    initializeFormGroup() {
        this.statusForm = this.fb.group({
            idStatus: [null, Validators.required],
            idBooking: [null]
        });

        this.statusFormReplace = this.fb.group({
            idStatusBookingReplace: [null, Validators.required],
            idStatusNew: [null, Validators.required],
            idBooking: [null]
        });

    }

    getStatuses(forceReload: boolean = false) {
        if (Date.now() - this.listStatusLoaded <= 1200000 && !forceReload) {
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
        this.http.post<{data: Booking, message: string}>(environment.apiURL + '/api/add-status-booking', statusForm.value).subscribe({
            next: (response : {data:Booking, message:string}) => {
                this.toolsService.openSnackBar('success',response.message)
                this._bookingByPatient$.next(response.data)
                this._loaderOneStatus$.next(false)
            },
            error: (response: HttpErrorResponse) => {
                console.log('response error :',response)
                this.toolsService.openSnackBar('error',response.error.message ?? 'Une erreur est survenue')
                this._loaderOneStatus$.next(false)
            }
        })
    }

    replaceStatus(statusFormReplace: FormGroup): Promise<void> {
        this._loaderOneStatus$.next(true)
        return new Promise<void>((resolve, reject) => {
            this.http.post<{data: Booking, message: string}>(environment.apiURL + '/api/replace-status-booking', statusFormReplace.value).subscribe({
                next: (response : {data:Booking, message:string}) => {
                    this.toolsService.openSnackBar('success',response.message)
                    this._bookingByPatient$.next(response.data)
                    this._loaderOneStatus$.next(false)
                    resolve()
                },
                error: (response: HttpErrorResponse) => {
                    console.log('response error :',response)
                    this.toolsService.openSnackBar('error',response.error.message ?? 'Une erreur est survenue')
                    this._loaderOneStatus$.next(false)
                    reject(response)
                }
            })
        })
    }

    addMultipleNewStatus(statusForm: FormGroup) {
        console.log('statusForm :',statusForm.value)
        this.http.post<{data: Booking[], message: string}>(environment.apiURL + '/api/add-multiple-status-booking', statusForm.value).subscribe({
            next: (response : {data:Booking[], message:string}) => {
                this.toolsService.openSnackBar('success',response.message)
                this._allBookingsByPatient$.next(response.data)
                this.loaderAllStatus$.next(false)
            },
            error: (response: HttpErrorResponse) => {
                this.toolsService.openSnackBar('error',response.error.message)
                this.loaderAllStatus$.next(false)
            }
        })
    }

    getAllBookingsByPatientId(patientId: number, forceReload: boolean = false) {
        this.listBookingsByPatientLoaded$.subscribe({
            next: (value: number) => {
                if (Date.now() - value <= 1200000 && !forceReload) {
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
