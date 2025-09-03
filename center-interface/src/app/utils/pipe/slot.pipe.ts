import { Injectable, Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'traductionSlot',
    standalone: true
})
@Injectable({
    providedIn: 'root'
})
export class SlotPipe implements PipeTransform {

    transform(slot: string): string {
        var traductionSlot: string = ''
        switch (slot) {
            case 'morning':
                traductionSlot = 'matin'
                break
            case 'evening':
                traductionSlot = 'après-midi'
                break
            case 'afternoon':
                traductionSlot = 'soir'
                break
            default:
                break
        }

        return traductionSlot
    }

}