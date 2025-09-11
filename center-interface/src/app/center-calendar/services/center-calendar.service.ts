import {HttpClient, HttpErrorResponse, HttpParams} from '@angular/common/http';
import {Injectable, signal, WritableSignal} from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { environment } from '../../../environment/environment';
import { GroupAvailability } from '../../interface/groupAvailability.interface';
import { JsonResponseInterface } from '../../shared/interfaces/json-response-interface';
import { ToolsService } from '../../shared/services/tools.service';

@Injectable({
    providedIn: 'root'
})

export class CenterCalendarServices {

    public _groupAvailability$ = new BehaviorSubject<GroupAvailability | null>(null)
    get groupAvailability$() {
        return this._groupAvailability$.asObservable()
    }

    public _groupAvailabilityChanged$ = new BehaviorSubject<GroupAvailability>({});
    get groupAvailabilityChanged$() {
        return this._groupAvailabilityChanged$.asObservable();
    }

    public _idCenter$ = new BehaviorSubject<number>(0);
    get idCenter$() {
        return this._idCenter$.asObservable();
    }

    public _originalGroupAvailability$ = new BehaviorSubject<GroupAvailability>({});
    get originalGroupAvailability$() {
        return this._originalGroupAvailability$.asObservable();
    }

    public loaderCalendar: WritableSignal<boolean> = signal(true)

    constructor(
        private http: HttpClient,
        private toolsService: ToolsService
    ) { }

    messageErrors : any = {};

    fetchGroupAvailability(idCenter: number):Observable<GroupAvailability> {
        return this.http.post<GroupAvailability>(environment.apiURL + '/api/availability-center', idCenter)
    }

    sendAvailability() {
        return this.http.post<JsonResponseInterface>(environment.apiURL + '/api/send-availability', {
            availability: this._groupAvailabilityChanged$.value,
            idCenter: this._idCenter$.value
        });
    }


  // getAvailabilityCenter(idCenter: number) {
  //   this.http.post<GroupAvailability>(environment.apiURL + '/api/availability-center', idCenter).subscribe({
  //     next: (value: GroupAvailability) => {
  //       this._groupAvailability$.next(value)
  //     },
  //     error: (e: Error) => {
  //       console.log(e.message)
  //     }
  //   })
  // }

}
