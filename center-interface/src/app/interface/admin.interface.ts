import { Center } from "./center.interface";

export interface Admin {
    id: number,
    centers: Center[],
    service: string,
}