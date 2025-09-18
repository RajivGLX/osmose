import { Center } from "./center.interface";
import {User} from "./user.interface";
export interface Patient {
    id: number,
    center: Center,
    phone: string,
    medical_history: string,
    checked: boolean,
    type_dialysis: string,
    drug_allergies: boolean,
    drug_allergie_precise: string,
    dialysis_start_date: Date,
    vascular_access_type: string,
}