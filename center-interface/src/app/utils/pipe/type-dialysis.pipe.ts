import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'translateTypeDialysis',
    standalone: true
})
export class TypeDialysis implements PipeTransform {

    transform(typeDialysis: string): string {
        var translateTypeDialysis: string = ''
        switch (typeDialysis) {
            case 'FAVN':
                translateTypeDialysis = 'Fistule artérioveineuse native (FAVN)'
                break
            case 'FAVP':
                translateTypeDialysis = 'Fistule artérioveineuse prothetique (FAVP)'
                break
            case 'GPV':
                translateTypeDialysis = 'Greffe de ponction veineuse (GPV)'
                break
            case 'CVC':
                translateTypeDialysis = 'Cathéter veineux central (CVC)'
                break
            default:
                break
        }

    return translateTypeDialysis
  }

}
