import { Patient } from "./patient.interface";

export interface Pathologies {
    id: number,
    heart_disease: string,
    diabetes: string,
    musculoskeletal_problems: string,
    bool_heart_disease: boolean,
    bool_diabetes: boolean,
    bool_musculoskeletal: boolean,

}