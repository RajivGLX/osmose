import { Component, EventEmitter, Input, OnInit, Output, WritableSignal } from '@angular/core'
import { CommonModule } from '@angular/common'
import { CenterFormService } from './../services/center-form.service'
import { ErrorHandler } from "../../shared/handlers/error.handler"
import { ReactiveFormsModule } from '@angular/forms'
import { MatFormFieldModule } from '@angular/material/form-field'
import { MatInputModule } from '@angular/material/input'
import { MatIconModule } from '@angular/material/icon'
import { MatCheckboxModule } from '@angular/material/checkbox'
import { LoaderComponent } from '../../loader/loader.component'
import { MatSelectModule } from '@angular/material/select'
import { MatOptionModule } from '@angular/material/core'
import { Region } from '../../interface/region.interface'
import { LoginService } from '../../login/services/login.service'

@Component({
    selector: 'app-center-create',
    standalone: true,
        imports: [
        CommonModule, 
        ReactiveFormsModule, 
        MatFormFieldModule, 
        MatInputModule, 
        MatIconModule, 
        MatCheckboxModule, 
        LoaderComponent,
        MatSelectModule,
        MatOptionModule,
    ],
    templateUrl: './center-create.component.html',
    styleUrl: './center-create.component.sass'
})

export class CenterCreateComponent implements OnInit {

    @Output() closeVueDetaillee = new EventEmitter<boolean>(false)

    center_create_form = this.centerFormService.center_create_form
    loadingCreate: WritableSignal<boolean> = this.centerFormService.loadingCreate
    daysOfWeek : Array<string> = this.centerFormService.daysOfWeek
    allRegions!: Array<Region>
    errors : any = {}

    constructor(
        private centerFormService: CenterFormService,
        private errorHandler:ErrorHandler,
        private loginService : LoginService
    ) {
        this.loginService.getAllRegions()
        this.loginService.allRegions$.subscribe((allRegions: Array<Region> | null) => {
            if (allRegions) {
                this.allRegions = allRegions
            }
        })
    }

    ngOnInit(): void {
        
        this.errorHandler.handleErrors(this.center_create_form, this.errors) 
    }

    resetFacturationDifferent() {
        this.centerFormService.resetFacturationDifferent(this.center_create_form)
    }

    sendNewCenter() {
        this.centerFormService.sendNewCenter()
    }

    goBack() {
        this.closeVueDetaillee.emit(true)
    }

    onCenterDayActiveChange(event: Event, day: string, centerDay: string) {
        const checked = (event.target as HTMLInputElement).checked;
        this.center_create_form.get('center_day')?.get(day)?.get(centerDay)?.patchValue({ closeSlot: checked });
        this.center_create_form.get('center_day')?.get(day)?.get(centerDay)?.patchValue({ open: "" })
        this.center_create_form.get('center_day')?.get(day)?.get(centerDay)?.patchValue({ close: "" })
    }
}
