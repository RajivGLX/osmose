
// ParamFilter pour les champs date des tableaux AgGrid

export const dateFilterParam = {
    comparator: (filterDate: any, cellValue: any) => {
        const date = new Date(cellValue).toLocaleDateString();
        const dateChosen = new Date(filterDate).toLocaleDateString();
        console.log(date, dateChosen);
        if (date < dateChosen) return -1;
        if (date > dateChosen) return 1;
        return 0;
    }
};
