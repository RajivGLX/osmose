import { Center } from './center.interface';
import { Slot } from './slot.interface';

export interface Availability {
    center: Center,
    date: Date,
    slot: Slot,
    availablePlace: number,
    reservedPlace: number
}