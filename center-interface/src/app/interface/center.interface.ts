import { Abonnement } from "./abonnement.interface";
import { Facture } from "./facture.interface";
import { Region } from './region.interface';

export interface Center {
    id: number,
    name: string,
    email: string,
    phone: string,
    url: string,
    band: string,
    latitude_longitude: string,
    slug: string,
    place_available: number,
    active: boolean,
    information: string,
    center_day: []
    address: string,
    city: string,
    zipcode: number,
    different_facturation: boolean,
    address_facturation: string,
    city_facturation: string,
    zipcode_facturation: string,
    abonnements: Abonnement[]
    factures: Facture[],
    region: Region
}
