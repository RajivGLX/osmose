import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { Patient } from '../../interface/patient.interface';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { ToolsService } from '../../shared/services/tools.service';
import { environment } from '../../../environment/environment.development';
import { User } from '../../interface/user.interface';

@Injectable({
  providedIn: 'root'
})
export class PatientListService {
    private _listAllPatients$ = new BehaviorSubject<User[]>([])
        get listAllPatients$() {
            return this._listAllPatients$.asObservable()
        }

    public loaderAgGrid$ = new BehaviorSubject<boolean>(false)

    private listAllPatientLoaded = 0

    constructor(
        private http: HttpClient,
        private toolsService: ToolsService
    ) { }

    getAllPatients(forceReload: boolean = false) {
        if (Date.now() - this.listAllPatientLoaded <= 1200000 && forceReload == false) {
            return
        }
        if(forceReload == false){
            this.loaderAgGrid$.next(true)
        }
        this.http.get<{ data: User[], message: string }>(environment.apiURL + '/api/get-all-patients').subscribe({
            next: (response: { data: User[], message: string }) => {
                this._listAllPatients$.next(response.data)
                this.listAllPatientLoaded = Date.now()
                this.loaderAgGrid$.next(false)
            },
            error: (error: HttpErrorResponse) => {
                this.loaderAgGrid$.next(false)
                console.log(error)
                this.toolsService.openSnackBar(error.error.message, false);
            }
        })
    }

    updateUserStatus(user: User, newStatus: boolean) {
        this.http.post<{ data: User, message: string }>(environment.apiURL + '/api/user-change-status', {id:user.id, valid:newStatus}).subscribe({
            next: (response : {message: string, data: User}) => {
                this.updatePatientInList(response.data)
                this.toolsService.openSnackBar(response.message, true)
            },
            error: (error: HttpErrorResponse) => {
                this.toolsService.openSnackBar(error.error.message, false)
            }
        });
    }

    updatePatientInList(updatedPatient: User) {
        const currentPatient = this._listAllPatients$.value;
        const index = currentPatient.findIndex(patient => patient.id === updatedPatient.id);
        if (index !== -1) {
            currentPatient[index] = updatedPatient;
            this._listAllPatients$.next([...currentPatient]);
        }
    }

    addPatientToList(newPatient: User) {
        const currentPatient = this._listAllPatients$.value;
        this._listAllPatients$.next([...currentPatient, newPatient]);
    }
}
