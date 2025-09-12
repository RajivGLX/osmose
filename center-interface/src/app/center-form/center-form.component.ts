import { CommonModule } from '@angular/common'
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core'
import { LoginService } from '../login/services/login.service'
import { ReactiveFormsModule } from '@angular/forms'
import { MatFormFieldModule } from '@angular/material/form-field'
import { MatInputModule } from '@angular/material/input'
import { MatIconModule } from '@angular/material/icon'
import { MatCheckboxModule } from '@angular/material/checkbox'
import { CenterUpdateComponent } from './center-update/center-update.component'
import { Center } from '../interface/center.interface'
import { User } from '../interface/user.interface'
import { Region } from '../interface/region.interface'
import { CenterCreateComponent } from './center-create/center-create.component'


@Component({
    selector: 'app-center-form',
    standalone: true,
    imports: [
        CommonModule, 
        ReactiveFormsModule, 
        MatFormFieldModule, 
        MatInputModule, 
        MatIconModule, 
        MatCheckboxModule, 
        CenterUpdateComponent,
        CenterCreateComponent
    ],

    templateUrl: './center-form.component.html',
    styleUrl: './center-form.component.sass'
})

export class CenterFormComponent implements OnInit {

    @Output() closeVueDetaillee = new EventEmitter<boolean>(false)
    @Input() view!: string
    @Input() set center(value: Center) {
        if (value) {
            this.currentCenter = value
        }
    }
    
    currentCenter! : Center
    idCenter!: number
    currentUser!: User
    allRegions!: Array<Region>

    
    constructor(
        private loginService: LoginService,
    ) { }

    ngOnInit(): void {

        this.loginService.getAllRegions()
        this.loginService.allRegions$.subscribe((allRegions: Array<Region> | null) => {
            if (allRegions) {
                this.allRegions = allRegions
            }
        })  

        this.loginService._userConnected$.subscribe((userData) => {
            if (userData) {
                this.currentUser = userData
                this.idCenter = userData.adminOsmose ? this.currentCenter.id : userData.administrator.centers[0].id
                
            }
        })
    }

    goBack() {
        this.closeVueDetaillee.emit(true)
    }
}
