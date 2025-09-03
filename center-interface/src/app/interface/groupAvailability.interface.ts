export interface GroupAvailability {
    [monthKey: string]: {
        [dayKey: string]: {
            [slotKey: string]: {
                qty: number
                check: boolean
                booking: number
            }
        }
    }
}