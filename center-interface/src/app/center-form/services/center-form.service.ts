import { Injectable, signal, WritableSignal} from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { environment } from '../../../environment/environment';
import { tap, Observable, of } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { facturationValidator } from '../../shared/validators/facturationValidator';
import { slotsValidator } from '../../shared/validators/SlotsValidator';
import { Center } from '../../interface/center.interface';
import { ToolsService } from "../../shared/services/tools.service";
import { CenterListService } from './../../center-list/services/center-list.service';
import { User } from '../../interface/user.interface';
import { LoginService } from '../../login/services/login.service';


@Injectable({
    providedIn: 'root'
})

export class CenterFormService {

    constructor(
        private fb: FormBuilder, 
        private http: HttpClient,
        private toolsService: ToolsService,
        private centerListService: CenterListService,
        private loginService: LoginService,
    ) { }

    loadingInfoCenter: WritableSignal<boolean> = signal(false)
    loadingCreate: WritableSignal<boolean> = signal(false)
    loadingInfoAddress: WritableSignal<boolean> = signal(false)
    loadingCenterDay: WritableSignal<boolean> = signal(false)

    center_info_form: FormGroup = this.fb.group({
        id: [null],
        name: [null, Validators.required],
        email: [null, [Validators.email]],
        phone: [null,[Validators.required, Validators.minLength(10), Validators.maxLength(15), Validators.pattern('^\\+?[0-9]*$')]],
        url: [null, Validators.required],
        place_available: [null, [Validators.required, Validators.pattern('^[0-9]*$')]],
        information: [null],
        band: [null],
        latitude_longitude: [null],
        region_id: [null],
    });

    center_adress_form: FormGroup = this.fb.group({
        id: [null],
        address: [null, Validators.required],
        zipcode: [null, [Validators.required, Validators.minLength(5), Validators.maxLength(5)]],
        city: [null, Validators.required],
        different_facturation: [null],
        address_facturation: [null],
        zipcode_facturation: [null],
        city_facturation: [null],
    }, { validators: facturationValidator() });

    center_create_form: FormGroup = this.fb.group({
        id: [null],
        name: [null, Validators.required],
        email: [null, [Validators.required, Validators.email]],
        phone: [null,[Validators.required, Validators.minLength(10), Validators.maxLength(15), Validators.pattern('^\\+?[0-9]*$')]],
        url: [null, Validators.required],
        place_available: [null, [Validators.required, Validators.pattern('^[0-9]*$')]],
        information: [null],
        band: [null],
        latitude_longitude: [null],
        region_id: [null],
        active: [false, Validators.required],
        address: [null, Validators.required],
        zipcode: [null, [Validators.required, Validators.minLength(5), Validators.maxLength(5)]],
        city: [null, Validators.required],
        different_facturation: [false],
        address_facturation: [null],
        zipcode_facturation: [null],
        city_facturation: [null],
        center_day: this.createDayForm()
    }, { validators: facturationValidator() });
    
    days_open: FormGroup = this.fb.group({
        lundi: [true],mardi: [true],mercredi: [true],jeudi: [true],vendredi: [true],samedi: [true],dimanche: [true],
    })

    center_day_form: FormGroup = this.createDayForm();
    
    daysOfWeek = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    getCenter(idCenter: number): Observable<Center | null> {
        return this.http.get<Center>(environment.apiURL + '/api/get-center', { params: { id: idCenter } }).pipe(
            catchError((error: HttpErrorResponse) => {
                console.log(error);
                return of(null);
            })
        );
    }

    sendNewCenter() {
        this.loadingCreate.set(true)
        this.http.post<{message : string, data : Center}>(environment.apiURL + '/api/create-center', this.center_create_form.value).subscribe({
            next: (response : {message: string, data: Center}) => {
                this.toolsService.openSnackBar('success',response.message)
                this.centerListService.addCenterToList(response.data)
                this.loadingCreate.set(false)
            },
            error: (response: HttpErrorResponse) => {
                this.loadingCreate.set(false)
                console.log(response)
                this.toolsService.openSnackBar('error',response.error.message)
            }
        })
    }

    updateCenterInfo(currentUser: User) {
        this.loadingInfoCenter.set(true)
        this.http.post<{message : string, data : Center}>(environment.apiURL + '/api/update-center-info', this.center_info_form.value).subscribe({
            next: (response : {message: string, data: Center}) => {
                this.toolsService.openSnackBar('success',response.message)
                this.initializeFormCenterInfoAndAddress(response.data)
                if(currentUser.adminOsmose){
                    this.centerListService.updateCenterInList(response.data) 
                }else{
                    this.loginService.getConnectedUser()
                }
            },
            error: (response: HttpErrorResponse) => {
                this.loadingInfoCenter.set(false)
                console.log(response)
                this.toolsService.openSnackBar('error',response.error.message);
            }
        })
    }

    updateCenterAddress(currentUser: User) {
        this.loadingInfoAddress.set(true)
        this.http.post<{message : string, data : Center}>(environment.apiURL + '/api/update-center-address', this.center_adress_form.value).subscribe({
            next: (response : {message: string, data: Center}) => {
                console.log('updateCenterAddress response : ',response)
                this.toolsService.openSnackBar('success',response.message)
                if(currentUser.adminOsmose){
                    this.initializeFormCenterInfoAndAddress(response.data)
                }else{
                    this.loginService.getConnectedUser()
                }
            },
            error: (response: HttpErrorResponse) => {
                this.loadingInfoAddress.set(false)
                console.log(response)
                this.toolsService.openSnackBar('error',response.error.message)
            }
        })
    }

    updateCenterDay(currentUser: User, centerDay: any[], center: Center) {
        this.loadingCenterDay.set(true)
        this.http.post<{message : string, data : Center}>(environment.apiURL + '/api/update-center-day', { id: center.id, center_day: centerDay }).subscribe({
            next: (response : {message: string, data: Center}) => {
                this.initializeFormCenterDay(response.data)
                this.toolsService.openSnackBar('success',response.message)
                if(!currentUser.adminOsmose){
                    this.loginService.getConnectedUser()
                }
            },
            error: (response: HttpErrorResponse) => {
                this.loadingCenterDay.set(false)
                this.toolsService.openSnackBar('error',response.error.message)
            }
        })
    }

    createDayForm(): FormGroup {
        const days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        const centerDayFormControls: { [key: string]: FormGroup } = {};

        days.forEach(day => {
            centerDayFormControls[day] = this.fb.group({
                morning: this.fb.group(
                    {
                        open: [null, Validators.required], 
                        close: [null, Validators.required], 
                        closeSlot: [true]
                    },
                    {
                        validators: [slotsValidator('open', 'close', 'closeSlot', 'morning')], 
                        updateOn: 'blur'
                    }
                ),
                afternoon: this.fb.group(
                    {
                        open: [null, Validators.required], 
                        close: [null, Validators.required], 
                        closeSlot: [true]
                    },
                    {
                        validators: slotsValidator('open', 'close', 'closeSlot', 'afternoon'), 
                        updateOn: 'blur'
                    }
                ),
                evening: this.fb.group(
                    {
                        open: [null, Validators.required], 
                        close: [null, Validators.required], 
                        closeSlot: [true]
                    },
                    {
                        validators: slotsValidator('open', 'close', 'closeSlot', 'evening'), 
                        updateOn: 'blur'
                    }
                ),
            });
        });

        return this.fb.group(centerDayFormControls);
    }

    initializeFormCenterInfoAndAddress(center: Center): void {
        this.center_info_form.patchValue(center);
        this.center_info_form.get('region_id')?.patchValue(center.region.id);
        this.center_adress_form.patchValue(center);

        this.loadingInfoCenter.set(false)
        this.loadingInfoAddress.set(false)
    }

    initializeFormCenterDay(center: Center): void {
        if (center.center_day) {
            for (const [key, value] of Object.entries(center.center_day)) {
                this.center_day_form?.get(key)?.patchValue(value)
            }
        }
        this.loadingCenterDay.set(false)
    }

    resetFacturationDifferent(form: FormGroup) {
        // console.log('resetFacturationDifferent', form.get('different_facturation')?.value)
        if (form.get('different_facturation')?.value) {
            form.get('different_facturation')?.reset()
            form.get('address_facturation')?.reset()
            form.get('zipcode_facturation')?.reset()
            form.get('city_facturation')?.reset()
        }
    }
}

