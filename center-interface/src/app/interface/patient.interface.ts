import { Center } from "./center.interface";
import { Pathologies } from './pathologies.interface';
export interface Patient {
    id: number,
    center: Center,
    pathologies: Pathologies,
    phone: string,
    medical_history: string,
    checked: boolean,
    renal_failure: string,
    type_dialysis: string,
    drug_allergies: boolean,
    drug_allergie_precise: string,
    dialysis_start_date: Date,
    vascular_access_type: string,
    renal_failure_other: string,
}