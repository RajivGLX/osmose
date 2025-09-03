import { Center } from "./center.interface";
import { Availability } from "./availability.interface";
import { Status } from "./status.interface";
import { User } from "./user.interface";
import { Patient } from './patient.interface';

export interface Booking {
    id: number,
    center: Center,
    patient: Patient & {
        user: User
    },
    availability: Availability,
    statusBookings: [
        {
            id: number,
            status_active: boolean,
            status: Status,
        }
    ],
    dateReserve: Date,
    createAt: Date,
    comment: string,
    reason: string
}