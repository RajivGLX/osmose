import { Component, OnInit } from '@angular/core'
import { LoginService } from '../login/services/login.service'
import { Center } from '../interface/center.interface'
import { User } from '../interface/user.interface'
import { KeyValuePipe, KeyValue } from '@angular/common'
import { CenterFormComponent } from '../center-form/center-form.component'

@Component({
    selector: 'app-center-info',
    standalone: true,
    imports: [
        KeyValuePipe,
        CenterFormComponent
    ],
    templateUrl: './center-info.component.html',
    styleUrl: './center-info.component.sass'
})
export class CenterInfoComponent implements OnInit {

    currentUser!: User
    currentCenter!: Center
    switchView: 'centerForm' | 'centerInfo'  = 'centerInfo'

    constructor(
        private loginService: LoginService,
    ) { }

    ngOnInit(): void {
        this.loginService._userConnected$.subscribe((userData) => {
            if (userData) {
                this.currentUser = userData
                this.currentCenter = userData.administrator.centers[0]
            }
        })
    }

    editCenter() {
        this.changeView('centerForm')
    }

    changeView(viewToDisplay: 'centerInfo' | 'centerForm') {
        this.switchView = viewToDisplay
    }

    compareDays(a: KeyValue<string, any>, b: KeyValue<string, any>): number {
        const daysOrder = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        return daysOrder.indexOf(a.key) - daysOrder.indexOf(b.key);
    }

}
