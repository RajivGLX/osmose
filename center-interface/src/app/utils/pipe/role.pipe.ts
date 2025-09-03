import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'traductionRole',
    standalone: true
})
export class RolePipe implements PipeTransform {

    transform(role: string): string {
        var traductionRole: string = ''
        switch (role) {
            case 'ROLE_SUPER_ADMIN':
                traductionRole = 'Administrateur en chef'
                break
            case 'ROLE_ADMIN':
                traductionRole = 'Administrateur'
                break
            case 'ROLE_PATIENT':
                traductionRole = 'Patient'
                break
            case 'ROLE_ADMIN_DIALYZONE':
                traductionRole = 'Administrateur Dialyzone'
                break
            default:
                break
        }

        return traductionRole
    }

}
