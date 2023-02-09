import fs from "fs";
import YAML from "yaml";

const file = fs.readFileSync("./Resources/Private/Icons/icons.yml", "utf8");
const icons = YAML.parse(file);
const list = [];
for (const key in icons) {
    list.push(key);
}

const setting = YAML.stringify(
    {
        Neos: {
            Neos: {
                Ui: {
                    frontendConfiguration: {
                        "Carbon.CodePen": {
                            afx: {
                                fusionObjects: {
                                    "Garagist.Fontawesome:Component.Icon": {
                                        documentation:
                                            "Add icon from [Fontawesome 6 Free](https://fontawesome.com/search)",
                                        snippet:
                                            '<Garagist.Fontawesome:Component.Icon style="${1|regular,solid,brands|}"${3: size="${4|2xs,xs,sm,lg,xl,2xl,1x,2x,3x,4x,5x,6x,7x,8x,9x,10x|}"} icon="${2|' +
                                            list.join(",") +
                                            '|}" />',
                                    },
                                },
                            },
                        },
                    },
                },
            },
        },
    },
    {
        collectionStyle: "block",
        indent: 2,
        lineWidth: 0,
    }
);

fs.writeFileSync("./Configuration/Settings.ContentBox.yaml", setting);

console.log(`\nWrote ${list.length} icons to ContentBox settings file.\n`);
