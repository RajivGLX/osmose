import { CommonModule } from '@angular/common';
import {Component, EventEmitter, Input, OnInit, Output, signal, WritableSignal} from '@angular/core'
import { FormBuilder, FormControl, FormGroup, FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { startOfMonth, endOfMonth, eachDayOfInterval, addMonths, subMonths, startOfWeek, endOfWeek, getDay } from 'date-fns';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { Observable } from 'rxjs';
import { CenterCalendarServices } from './services/center-calendar.service';
import { GroupAvailability } from '../interface/groupAvailability.interface';
import { MatDialog } from '@angular/material/dialog';
import { PopupRecapComponent } from './popup-recap/popup-recap.component';
import { LoginService } from '../login/services/login.service';
import { User } from '../interface/user.interface';
import { Center } from '../interface/center.interface';
import { values } from 'lodash';
import {MatProgressSpinner} from "@angular/material/progress-spinner";


type Slot = 'morning' | 'afternoon' | 'evening';

@Component({
    selector: 'app-center-calendar',
    standalone: true,
    imports: [
        CommonModule,
        ReactiveFormsModule,
        FormsModule,
        MatFormFieldModule,
        MatInputModule,
        MatSelectModule,
        MatProgressSpinner,
    ],
    templateUrl: './center-calendar.component.html',
    styleUrl: './center-calendar.component.sass'
})
export class CenterCalendarComponent implements OnInit {

    calendarForm: FormGroup
    currentUser!: User
    currentCenter!: Center
    availableSlots: any[] = []
    currentMonth: Date = new Date()
    today: Date = new Date()
    daysInMonth: Date[] = []
    weeks: (Date | null)[][] = []
    groupAvailability$!: Observable<GroupAvailability>
    loaderCalendar: WritableSignal<boolean>
    minMonth: Date
    maxMonth: Date

    slots: Slot[] = ['morning', 'afternoon', 'evening']  // Périodes typées

    constructor(
        private dialog: MatDialog,
        private formBuilder: FormBuilder,
        private centerCalendarService: CenterCalendarServices,
        private loginService: LoginService
    ) {
        this.calendarForm = this.formBuilder.group({})
        this.minMonth = subMonths(this.today, 0)
        this.maxMonth = addMonths(this.today, 12)
        this.loaderCalendar = this.centerCalendarService.loaderCalendar
    }

    @Output() closeVueDetaillee = new EventEmitter<boolean>(false)
    @Input() set center(value: Center) {
        if (value) {
            this.currentCenter = value
        }
    }

    ngOnInit(): void {
        this.loginService._userConnected$.subscribe((userData) => {
            if (userData) {
                this.currentUser = userData
                this.currentCenter = userData.adminDialyzone ? this.currentCenter : userData.administrator.centers[0]
                this.centerCalendarService._idCenter$.next(this.currentCenter.id)
                this.groupAvailability$ = this.centerCalendarService.fetchGroupAvailability(this.currentCenter.id);
                this.weeks = this.getWeeksInMonth();
                this.createForm();
                this.initializeForm();
            }
        })

    }

    getWeeksInMonth(): (Date | null)[][] {
        const weeks: (Date | null)[][] = [];
        let currentWeek: (Date | null)[] = [];
        const start = startOfWeek(startOfMonth(this.currentMonth), { weekStartsOn: 1 });
        const end = endOfWeek(endOfMonth(this.currentMonth), { weekStartsOn: 1 });
        const days = eachDayOfInterval({ start, end });
        days.forEach((day, index) => {
        if (index === 0 && getDay(day) !== 1) {
            // Ajouter des cellules vides pour les jours avant le début du mois
            for (let i = 1; i < getDay(day); i++) {
                currentWeek.push(null);
            }
        }
        currentWeek.push(day);
        if (currentWeek.length === 7) {
            weeks.push(currentWeek);
            currentWeek = [];
        }
        });
        if (currentWeek.length > 0) {
        // Ajouter des cellules vides pour compléter la dernière semaine
        while (currentWeek.length < 7) {
            currentWeek.push(null);
        }
        weeks.push(currentWeek);
        }
        return weeks;
    }

    createForm(): void {
        const monthKey = this.generateMonthKey(this.currentMonth);
        if (!this.calendarForm.contains(monthKey) && this.currentMonth >= this.today) {
            const monthGroup = this.formBuilder.group({});
            this.weeks.forEach(week => {
                week.forEach(day => this.addDayToForm(day, monthGroup));
            });
            this.calendarForm.addControl(monthKey, monthGroup);
        }
    }

    addDayToForm(day: Date | null, monthGroup: FormGroup): void {
        if (day && day.getMonth() === this.currentMonth.getMonth() && day > this.today) {
            const dayKey = this.generateDateKey(day);
            if (!this.calendarForm.contains(dayKey)) {
                const dayGroup = this.createDayFormGroup();
                monthGroup.addControl(dayKey, dayGroup);
            }
        }
    }

    createDayFormGroup(): FormGroup {
        const dayGroup = this.formBuilder.group({});
        this.slots.forEach(slot => {
            const slotGroup = this.formBuilder.group({
                'qty': this.formBuilder.control(1),
                'check': this.formBuilder.control(false),
                'booking': this.formBuilder.control(0)
            });
            dayGroup.addControl(slot, slotGroup);
        });
        return dayGroup;
    }

    initializeForm(): void {
        const monthKey = this.generateMonthKey(this.currentMonth);
        this.groupAvailability$.subscribe(data => {
            // Stocker les données originales
            this.centerCalendarService._originalGroupAvailability$.next(data);

            const availabilityData = data[monthKey as keyof GroupAvailability];
            if (availabilityData) {
                Object.keys(availabilityData).forEach(dayKey => {
                    const dayData = (availabilityData as Record<string, any>)[dayKey];
                    const dayGroup = this.calendarForm.get(`${monthKey}.${dayKey}`) as FormGroup;
                    if (dayGroup) {
                        Object.keys(dayData).forEach(slot => {
                            const slotData = dayData[slot];
                            const slotGroup = dayGroup.get(slot) as FormGroup;
                            if (slotGroup) {
                                slotGroup.patchValue({
                                    'qty': slotData.qty,
                                    'check': slotData.check,
                                    'booking': slotData.booking
                                });
                            }
                        });
                    }
                });
            }
            this.centerCalendarService.loaderCalendar.set(false);
        })
    }

    nextMonth() {
        this.changeMonth(1);
    }

    prevMonth() {
        this.changeMonth(-1);
    }

    changeMonth(direction: number) {
        this.centerCalendarService.loaderCalendar.set(true);
        const newMonth = addMonths(this.currentMonth, direction)
        if (newMonth <= this.maxMonth && newMonth >= this.minMonth) {
            this.currentMonth = newMonth
            this.updateCalendar()
        }
    }

    updateCalendar() {
        this.weeks = this.getWeeksInMonth();
        this.createForm();
        this.initializeForm();
    }

    increment(slot: Slot, day: Date): void {
        const dateKeyMonth = this.generateMonthKey(day);
        const dateKey = this.generateDateKey(day);
        const dayGroup = this.calendarForm.get(dateKeyMonth)?.get(dateKey) as FormGroup;
        if (dayGroup) {
            const quantityControl = dayGroup.get(slot)?.get('qty') as FormControl;
            const checkControl = dayGroup.get(slot)?.get('check') as FormControl;
            if (quantityControl) {
                const currentValue = quantityControl.value || 1;
                quantityControl.setValue(currentValue + 1);
            }
            if (checkControl.value) {
                this.updateDataForSend(slot, day, checkControl, quantityControl);
            }
        }
    }

    decrement(slot: Slot, day: Date): void {
        const dateKeyMonth = this.generateMonthKey(day);
        const dateKey = this.generateDateKey(day);
        const dayGroup = this.calendarForm.get(dateKeyMonth)?.get(dateKey) as FormGroup;
        if (dayGroup) {
            const quantityControl = dayGroup.get(slot)?.get('qty') as FormControl;
            const checkControl = dayGroup.get(slot)?.get('check') as FormControl;

            if (quantityControl) {
                const currentValue = quantityControl.value || 0;
                if (currentValue > 1) {
                    quantityControl.setValue(currentValue - 1);
                }
            }
            if (checkControl.value) {
                this.updateDataForSend(slot, day, checkControl, quantityControl);
            }
        }
    }

    onInputChange(event: Event, slot: Slot, day: Date): void {
        const inputElement = event.target as HTMLInputElement;
        const newValue = parseInt(inputElement.value, 10);
        const dateKeyMonth = this.generateMonthKey(day);
        const dateKey = this.generateDateKey(day);
        const dayGroup = this.calendarForm.get(dateKeyMonth)?.get(dateKey) as FormGroup;
        if (dayGroup) {
            const quantityControl = dayGroup.get(slot)?.get('qty') as FormControl;
            const checkControl = dayGroup.get(slot)?.get('check') as FormControl;
            if (quantityControl) {
                quantityControl.setValue(newValue);
            }
            if (checkControl.value) {
                this.updateDataForSend(slot, day, checkControl, quantityControl);
            }
        }
    }

    onCheckboxChange(event: Event, slot: Slot, day: Date): void {
        const inputElement = event.target as HTMLInputElement;
        const isChecked = inputElement.checked;
        const dateKeyMonth = this.generateMonthKey(day);
        const dateKey = this.generateDateKey(day);
        const dayGroup = this.calendarForm.get(dateKeyMonth)?.get(dateKey) as FormGroup;

        if (dayGroup) {
            const checkControl = dayGroup.get(slot)?.get('check') as FormControl;
            const qtyControl = dayGroup.get(slot)?.get('qty') as FormControl;

            if (checkControl) {
                checkControl.setValue(isChecked);
            }

            if (!isChecked) {
                // Remettre à zéro le champ quantity quand décoché
                if (qtyControl) {
                    qtyControl.setValue(1);
                }
                // Supprimer de la popup recap
                this.removeFromDataForSend(slot, day);
            } else {
                // Si coché, mais quantité à 0, remettre à 1
                if (qtyControl && qtyControl.value < 1) {
                    qtyControl.setValue(1);
                }
                // Ajouter/mettre à jour dans la popup recap
                this.updateDataForSend(slot, day, checkControl, qtyControl);
            }
        }
    }

    removeFromDataForSend(slot: Slot, day: Date): void {
        const currentAvailability = {...this.centerCalendarService._groupAvailabilityChanged$.value};
        const monthKey = this.generateMonthKey(day);
        const dayKey = this.generateDateKey(day);

        // Vérifier si la valeur existait initialement (patchValue)
        const originalValue = this.getOriginalSlotValue(day, slot);
        console.log('originalValue', originalValue);
        if (originalValue && originalValue.check) {
            // Si c'était initialement coché, garder l'entrée avec check: false
            if (!currentAvailability[monthKey]) {
                currentAvailability[monthKey] = {};
            }
            if (!currentAvailability[monthKey][dayKey]) {
                currentAvailability[monthKey][dayKey] = {};
            }

            currentAvailability[monthKey][dayKey][slot] = {
                qty: 0, // Quantité à 0 pour indiquer la désactivation
                check: false,
                booking: originalValue.booking || 0
            };
        } else {
            // Comportement normal pour les nouvelles entrées
            if (currentAvailability[monthKey]?.[dayKey]?.[slot]) {
                delete currentAvailability[monthKey][dayKey][slot];

                if (Object.keys(currentAvailability[monthKey][dayKey]).length === 0) {
                    delete currentAvailability[monthKey][dayKey];
                }

                if (Object.keys(currentAvailability[monthKey]).length === 0) {
                    delete currentAvailability[monthKey];
                }
            }
        }

        this.centerCalendarService._groupAvailabilityChanged$.next(currentAvailability);
    }

    getOriginalSlotValue(day: Date, slot: Slot): any {
        const monthKey = this.generateMonthKey(day);
        const dayKey = this.generateDateKey(day);

        // Récupérer les données originales stockées
        const originalData = this.centerCalendarService._originalGroupAvailability$.value;
        return originalData?.[monthKey]?.[dayKey]?.[slot];
    }

    getBookingValue(day: Date, slot: Slot): number {
        const monthKey = this.generateMonthKey(day);
        const dateKey = this.generateDateKey(day);
        const dayGroup = this.calendarForm.get(monthKey)?.get(dateKey) as FormGroup;
        if (dayGroup) {
            const bookingControl = dayGroup.get(slot)?.get('booking');
            return bookingControl ? bookingControl.value : 0;
        }
        return 0;
    }

    updateDataForSend(slot: Slot, day: Date, checkControl: FormControl, qtyControl: FormControl): void {
    // Récupérer la valeur actuelle sans s'abonner
        const currentAvailability = {...this.centerCalendarService._groupAvailabilityChanged$.value};
        
        // Générer les clés pour le mois et le jour
        const monthKey = this.generateMonthKey(day);
        const dayKey = this.generateDateKey(day);
        
        // Initialiser le mois s'il n'existe pas
        if (!currentAvailability[monthKey]) {
            currentAvailability[monthKey] = {};
        }
        
        // Initialiser le jour s'il n'existe pas
        if (!currentAvailability[monthKey][dayKey]) {
            currentAvailability[monthKey][dayKey] = {};
        }
        
        // Initialiser ou mettre à jour le créneau
        currentAvailability[monthKey][dayKey][slot] = {
            qty: qtyControl.value,
            check: checkControl.value,
            booking: 0
        };
        
        // Mettre à jour le BehaviorSubject avec les nouvelles données
        this.centerCalendarService._groupAvailabilityChanged$.next(currentAvailability);
        
        console.log('updateDataForSend', currentAvailability);
    }

    // Vérifie si on peut aller au mois précédent
    isFirstMonthReached(): boolean {
        const prevMonth = subMonths(this.currentMonth, 1);
        return prevMonth < this.minMonth;
    }

    // Vérifie si on peut aller au mois suivant
    isLastMonthReached(): boolean {
        const nextMonth = addMonths(this.currentMonth, 1);
        return nextMonth > this.maxMonth;
    }

    // Ouvrir le récapitulatif (modal)
    openRecap() {
        this.dialog.open(PopupRecapComponent, {
            disableClose: true
        });
    }

    // Vérifie si une case à cocher est cochée
    getSlotChecked(day: Date, slot: Slot): boolean {
        const monthKey = this.generateMonthKey(day);
        const dateKey = this.generateDateKey(day);
        const dayGroup = this.calendarForm.get(monthKey)?.get(dateKey) as FormGroup;
        return dayGroup?.get(slot)?.get('check')?.value || false;
    }

    // Cocher ou décocher toutes les cases à cocher
    toggleAllCheckboxes(event: Event): void {
        const isChecked = (event.target as HTMLInputElement).checked;
        const monthKey = this.generateMonthKey(this.currentMonth);
        const monthGroup = this.calendarForm.get(monthKey) as FormGroup;

        if (monthGroup) {
            Object.keys(monthGroup.controls).forEach(dateKey => {
                const dayGroup = monthGroup.get(dateKey) as FormGroup;
                if (dayGroup) {
                    this.slots.forEach(slot => {
                        const slotGroup = dayGroup.get(slot) as FormGroup;
                        if (slotGroup) {
                            const checkControl = slotGroup.get('check') as FormControl;
                            const qtyControl = slotGroup.get('qty') as FormControl;
                            checkControl.setValue(isChecked);
                            
                            // Mise à jour des données pour l'envoi
                            if (isChecked) {
                                const [year, month, day] = dateKey.split('/');
                                const date = new Date(Number(year), Number(month) - 1, Number(day));
                                this.updateDataForSend(slot, date, checkControl, qtyControl);
                            }
                        }
                    });
                }
            });
        }
    }

    // Vérifie si toutes les cases à cocher sont cochées
    areAllChecked(): boolean {
        const monthKey = this.generateMonthKey(this.currentMonth);
        const monthGroup = this.calendarForm.get(monthKey) as FormGroup;
        
        if (!monthGroup) return false;

        let allChecked = true;
        Object.keys(monthGroup.controls).forEach(dateKey => {
            const dayGroup = monthGroup.get(dateKey) as FormGroup;
            if (dayGroup) {
                this.slots.forEach(slot => {
                    const slotGroup = dayGroup.get(slot) as FormGroup;
                    if (slotGroup) {
                        const checkControl = slotGroup.get('check');
                        if (!checkControl?.value) {
                            allChecked = false;
                        }
                    }
                });
            }
        });
        return allChecked;
    }

    // Génère une clé pour le jour
    generateDateKey(day: Date): string {
        return `${day.getFullYear()}/${(day.getMonth() + 1).toString().padStart(2, '0')}/${day.getDate().toString().padStart(2, '0')}`;
    }

    // Génère une clé pour le mois
    generateMonthKey(day: Date): string {
        return `${day.getFullYear()}/${(day.getMonth() + 1).toString().padStart(2, '0')}`;
    }

    getFormattedMonth(): string {
    return format(this.currentMonth, 'MMMM yyyy', { locale: fr });
    }

    trackByPeriod(index: number, slots: Slot): string {
        return slots;
    }

    trackByIndex(index: number, item: any): number {
        return index;
    }
}
