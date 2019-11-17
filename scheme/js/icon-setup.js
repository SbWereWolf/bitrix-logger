const iconSetup = {
    dy: 140,
    dx: 138,
    /* массив с координатами иконок "В аренде" */
    inLease: [],
    /* массив с координатами иконок "Свободно" */
    available: [],
    compose: function () {
        this.define(this.inLease, 0, 0);
        this.define(this.available, this.dx * 3, 0);
    },
    define: function (icons, sx, sy) {
        const initialX = sx;
        icons[types["white-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //1
        sx += this.dx;
        icons[types["white-cube"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //2
        sx += this.dx;
        icons[types["V"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //3
        sy += this.dy;
        sx = initialX;
        icons[types["twice-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //4
        sx += this.dx;
        icons[types["black-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //5
        sx += this.dx;
        icons[types["crocodile"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //6
        sy += this.dy;
        sx = initialX;
        icons[types["flag"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //7
        sx += this.dx;
        icons[types["six-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //8
        sx += this.dx;
        icons[types["black-cube"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //9
        sy += this.dy;
        sx = initialX;
        icons[types["white-triangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //10
        sx += this.dx;
        icons[types["cross"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //11
        sx += this.dx;
        icons[types["star"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //12
        sy += this.dy;
        sx = initialX;
        icons[types["arrow"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //13
        sx += this.dx;
        icons[types["black-triangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //14
        sx += this.dx;
        icons[types["white-long-rectangle"]] =
            [[Number(sx), Number(sy)],
                [Number(sx + this.dx), Number(sy + this.dy)]]; //15
        sy += this.dy;
        sx = initialX;
        icons[types["black-circle"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //16
        sx += this.dx;
        icons[types["white-circle"]] = [[Number(sx), Number(sy)],
            [Number(sx + this.dx), Number(sy + this.dy)]]; //17
        sx += this.dx;
        icons[types["white-circle-with-dot"]] =
            [[Number(sx), Number(sy)],
                [Number(sx + this.dx), Number(sy + this.dy)]]; //18

        return icons;
    }
};
