import { CommonModule } from '@angular/common'
import { Component, EventEmitter, Input, OnInit, Output, WritableSignal } from '@angular/core'
import { FormGroup, FormsModule, ReactiveFormsModule } from '@angular/forms'
import { MatFormFieldModule } from '@angular/material/form-field'
import { MatIconModule } from '@angular/material/icon'
import { MatInputModule } from '@angular/material/input'
import { LoaderComponent } from '../../loader/loader.component'
import { NgbPopoverModule } from '@ng-bootstrap/ng-bootstrap'
import { CenterFormService } from '../services/center-form.service'
import { Center } from '../../interface/center.interface'
import {ErrorHandler} from "../../shared/handlers/error.handler"
import { Region } from '../../interface/region.interface'
import { User } from '../../interface/user.interface'
import { MatSelectModule } from '@angular/material/select'
import { MatOptionModule } from '@angular/material/core'


@Component({
    selector: 'app-center-update',
    standalone: true,
    imports: [
        CommonModule, 
        ReactiveFormsModule,
        FormsModule, 
        MatInputModule, 
        MatIconModule, 
        MatFormFieldModule, 
        LoaderComponent,
        NgbPopoverModule,
        MatSelectModule,
        MatOptionModule,
    ],
    templateUrl: './center-update.component.html',
    styleUrl: './center-update.component.sass'
})
export class CenterUpdateComponent implements OnInit {

    @Output() goBackEvent = new EventEmitter<void>()
    @Input() currentUser!: User
    @Input() idCenter!: number
    @Input() allRegions!: Array<Region>

    center_info_form: FormGroup = this.centerFormService.center_info_form
    center_adress_form: FormGroup = this.centerFormService.center_adress_form
    center_day_form: FormGroup = this.centerFormService.center_day_form

    loadingInfoCenter: WritableSignal<boolean> = this.centerFormService.loadingInfoCenter
    loadingInfoAddress: WritableSignal<boolean> = this.centerFormService.loadingInfoAddress
    days_open: FormGroup = this.centerFormService.days_open
    daysOfWeek : Array<string> = this.centerFormService.daysOfWeek
    loadingCenterDay: WritableSignal<boolean> = this.centerFormService.loadingCenterDay
    title = "Pour confirmer les modifications, tous les champs doivent être remplis ou fermé"
    errors : any = {}

    centerSelect!: Center 

    constructor(
        private centerFormService: CenterFormService,
        private errorHandler:ErrorHandler
    ) { }

    ngOnInit(): void {
        this.centerFormService.getCenter(this.idCenter).subscribe(center => {
            if (center) {
                this.centerSelect = center;
                console.log('centerSelect', this.centerSelect);

                this.centerFormService.initializeFormCenterInfoAndAddress(this.centerSelect);
                this.centerFormService.initializeFormCenterDay(this.centerSelect);
                this.errorHandler.handleErrors(this.center_info_form, this.errors);
                this.errorHandler.handleErrors(this.center_adress_form, this.errors);
                this.errorHandler.handleErrors(this.center_day_form, this.errors);
            }
        });
    }

    resetFacturationDifferent() {
        this.centerFormService.resetFacturationDifferent(this.center_adress_form)
    }

    sendUpdateCenter() {
        this.centerFormService.updateCenterInfo(this.currentUser)
    }

    sendUpdateCenterAddress() {
        this.centerFormService.updateCenterAddress(this.currentUser)
    }

    sendUpdateCenterDay() {
        this.centerFormService.updateCenterDay(this.currentUser, this.center_day_form.value, this.centerSelect)
    }    

    onCenterDayActiveChange(event: Event, day: string, centerDay: string) {
        const checked = (event.target as HTMLInputElement).checked;
        this.center_day_form.get(day)?.get(centerDay)?.patchValue({ closeSlot: checked })
        this.center_day_form.get(day)?.get(centerDay)?.patchValue({ open: "" })
        this.center_day_form.get(day)?.get(centerDay)?.patchValue({ close: "" })
    }

    goBack() {
        this.goBackEvent.emit()
    }
}
