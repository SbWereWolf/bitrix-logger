function composeIcons() {

    function defineIconsSet(icons, sx, sy) {
        const initialX = sx;
        icons[types["white-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //1
        sx += dx;
        icons[types["white-cube"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //2
        sx += dx;
        icons[types["V"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //3
        sy += dy;
        sx = initialX;
        icons[types["twice-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //4
        sx += dx;
        icons[types["black-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //5
        sx += dx;
        icons[types["crocodile"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //6
        sy += dy;
        sx = initialX;
        icons[types["flag"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //7
        sx += dx;
        icons[types["six-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //8
        sx += dx;
        icons[types["black-cube"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //9
        sy += dy;
        sx = initialX;
        icons[types["white-triangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //10
        sx += dx;
        icons[types["cross"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //11
        sx += dx;
        icons[types["star"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //12
        sy += dy;
        sx = initialX;
        icons[types["arrow"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //13
        sx += dx;
        icons[types["black-triangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //14
        sx += dx;
        icons[types["white-long-rectangle"]] =
            [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //15
        sy += dy;
        sx = initialX;
        icons[types["black-circle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //16
        sx += dx;
        icons[types["white-circle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //17
        sx += dx;
        icons[types["white-circle-with-dot"]] =
            [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //18

        return icons;
    }

    const dy = 138;
    const dx = 138;

    defineIconsSet(inLease, 0, 0);
    defineIconsSet(available, dx * 3, 0);
}