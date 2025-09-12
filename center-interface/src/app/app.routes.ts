import { Routes } from '@angular/router';
import { LoginComponent } from './login/login.component';
import { OverViewComponent } from './over-view/over-view.component';
import { CenterCalendarComponent } from './center-calendar/center-calendar.component';
import { isLogged, hasAccess } from './login/guards/auth.guard';
import { BookingListComponent } from './booking-list/booking-list.component';
import { CenterListComponent } from './center-list/center-list.component';
import { CenterInfoComponent } from './center-info/center-info.component';
import { AdminListComponent } from './admin-list/admin-list.component';
import { PatientListComponent } from './patient-list/patient-list.component';
import { PatientFormComponent } from './patient-form/patient-form.component';
import { MyAccountComponent } from './my-account/my-account.component';
import { ForgotPasswordComponent } from "./login/forgot-password/forgot-password.component";

const ADMIN_OSMOSE = 'ROLE_ADMIN_OSMOSE'
const SUPER_ADMIN = 'ROLE_SUPER_ADMIN'
const ADMIN = 'ROLE_ADMIN'
const COMPTABLE = 'ROLE_COMPTABLE'

export const routes: Routes = [
    { path: 'login', component: LoginComponent },
    {path: 'forgot-password', component: ForgotPasswordComponent},
    { path: '', component: OverViewComponent, canActivate: [isLogged()] },
    { path: 'my-account', component: MyAccountComponent, canActivate: [isLogged()] },
    { path: 'booking-list', component: BookingListComponent, canActivate: [isLogged()] },
    { path: 'center-info', component: CenterInfoComponent, canActivate: [isLogged()] },
    { path: 'center-calendar', component: CenterCalendarComponent, canActivate: [isLogged()] },
    {
        path: 'center-list',
        component: CenterListComponent,
        canActivate: [isLogged(), hasAccess([ADMIN_OSMOSE])]
    },
    { path: 'admin-list', component: AdminListComponent, canActivate: [isLogged()] },
    { path: 'patient-list', component: PatientListComponent, canActivate: [isLogged()] },
    // temporary route
    { path: 'patient-form', component: PatientFormComponent, canActivate: [isLogged()] },
];
