export const landUseHelper = {
    /**
     * Return an appropriate hex value for each colour
     */
    getColour (landuse)
    {
        if (landuse === 'brownfield') {
            return '#993206';
        }
        else if (landuse === 'cemetery') {
            return '#808080';
        }

        return '#3388ff';
    }
}
