import { CommonModule } from '@angular/common'
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core'
import { LoginService } from '../login/services/login.service'
import { AdminUpdateComponent } from './admin-update/admin-update.component'
import { AdminCreateComponent } from './admin-create/admin-create.component'
import { User } from '../interface/user.interface'
import { CenterListService } from '../center-list/services/center-list.service'

@Component({
    selector: 'app-admin-form',
    standalone: true,
    imports: [
        CommonModule, 
        AdminUpdateComponent,
        AdminCreateComponent
    ],
    templateUrl: './admin-form.component.html',
    styleUrl: './admin-form.component.sass'
})
export class AdminFormComponent implements OnInit {
    
    @Output() closeVueDetaillee = new EventEmitter<boolean>(false)
    @Input() view!: string
    @Input() set userSelect (value: User) {
        if (value) {
            this.adminToUpdate = value
        }
    }
    
    adminToUpdate! : User
    
    constructor(
        private loginService: LoginService,
        private centerListService: CenterListService
    ) {}

    ngOnInit(): void {
        //Si on est sur la vue de modification et que l'utilisateur n'est pas 
        // passé en input cela signifie que l'admin modifie c'est propre informations.
        if(!this.adminToUpdate){
            this.loginService._userConnected$.subscribe((userData) => {
                if (userData) {
                    this.adminToUpdate = userData
                }
            })
        }
        this.centerListService.getAllCenters()
    }

    goBack() {
        this.closeVueDetaillee.emit(true)
    }
}
