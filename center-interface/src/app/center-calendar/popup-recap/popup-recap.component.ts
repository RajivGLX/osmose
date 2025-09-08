import { Dialog } from '@angular/cdk/dialog'
import { Component, OnInit } from '@angular/core'
import { CenterCalendarServices } from '../services/center-calendar.service'
import { CommonModule, KeyValuePipe } from '@angular/common'
import { MatExpansionModule } from '@angular/material/expansion'
import { SlotPipe } from '../../utils/pipe/slot.pipe'
import { GroupAvailability } from '../../interface/groupAvailability.interface'
import { SortDaysPipe } from '../../utils/pipe/sort-days.pipe'
import { SortSlotsPipe } from '../../utils/pipe/sort-slots.pipe'
import {JsonResponseInterface} from "../../shared/interfaces/json-response-interface";
import {HttpErrorResponse} from "@angular/common/http";
import {ToolsService} from "../../shared/services/tools.service";

@Component({
    selector: 'app-popup-recap',
    standalone: true,
    imports: [
        CommonModule, 
        KeyValuePipe, 
        MatExpansionModule, 
        SlotPipe,
        SortDaysPipe,
        SortSlotsPipe
    ],
    templateUrl: './popup-recap.component.html',
    styleUrl: './popup-recap.component.sass'
})

export class PopupRecapComponent implements OnInit {

    errors: any = this.centerCalendarServices.messageErrors
    groupSlots: GroupAvailability = {} as GroupAvailability
    expandedMonths: { [key: string]: boolean } = {}
    expandedDays: { [key: string]: boolean } = {}

    constructor(
        private dialog: Dialog,
        public centerCalendarServices : CenterCalendarServices,
        public toolsService : ToolsService
    ) { }

    ngOnInit(): void {
        this.centerCalendarServices.groupAvailabilityChanged$.subscribe((slotsData: GroupAvailability) => {
            this.groupSlots = slotsData;
            
            // Si il n'y a qu'un mois, on l'expand automatiquement
            const months = Object.keys(this.groupSlots);
            if (months.length === 1) {
                const monthKey = months[0];
                this.expandedMonths[monthKey] = true;
                // Expand tous les jours de ce mois
                Object.keys(this.groupSlots[monthKey]).forEach(dayKey => {
                    this.expandedDays[dayKey] = true;
                });
            } else {
                // Comportement normal pour plusieurs mois
                months.forEach(monthKey => {
                    this.expandedMonths[monthKey] = false;
                    Object.keys(this.groupSlots[monthKey] || {}).forEach(dayKey => {
                        this.expandedDays[dayKey] = false;
                    });
                });
            }
        });
    }

    hasOnlyOneMonth(): boolean {
        return Object.keys(this.groupSlots).length === 1;
    }
    

    toggleMonth(month: string) {
        this.expandedMonths[month] = !this.expandedMonths[month];
    }

    toggleDay(day: string) {
        this.expandedDays[day] = !this.expandedDays[day];
    }

    closeModal() {
        this.dialog.closeAll();
        this.centerCalendarServices.messageErrors = {}
    }

    sendDataToBack() {
        this.closeModal();
        this.centerCalendarServices.loaderCalendar.set(true);

        this.centerCalendarServices.sendAvailability().subscribe({
            next: (data: JsonResponseInterface) => {
                this.centerCalendarServices.loaderCalendar.set(false);
                this.toolsService.openSnackBar(data.message, true);
            },
            error: (err: HttpErrorResponse) => {
                this.centerCalendarServices.loaderCalendar.set(false);
                this.toolsService.openSnackBar(err.error.message, false);
            }
        });
    }


    trackByMonth(index: number, item: any): string {
        return item.key;
    }

    trackByDay(index: number, item: any): string {
        return item.key;
    }

    trackBySlot(index: number, item: any): string {
        return item.slot;
    }

    getObjectKeys(obj: any): string[] {
        return Object.keys(obj);
    }

}
