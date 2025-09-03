import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'sortSlots',
    standalone: true
})
export class SortSlotsPipe implements PipeTransform {
    transform(slots: string[]): string[] {
        if (!slots) return [];
        
        // Définir l'ordre des créneaux
        const slotOrder: { [key: string]: number } = {
            'morning': 1,
            'afternoon': 2,
            'evening': 3
        };
        
        // Trier les créneaux selon l'ordre prédéfini
        return [...slots].sort((a, b) => {
            return (slotOrder[a] || 999) - (slotOrder[b] || 999);
        });
    }
    
}