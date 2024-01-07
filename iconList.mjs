import fs from "fs";
import YAML from "yaml";

const documentation =
    "Add icon from [Fontawesome 6 Free](https://fontawesome.com/search)";

const settingsFile = fs.readFileSync(
    "Configuration/Settings.Garagist.yaml",
    "utf8",
);
const { Garagist } = YAML.parse(settingsFile);
const styles = Garagist.Fontawesome.styles.join(",");

let settingsFileLocation = Garagist.Fontawesome.settingsLocation.split("/");
settingsFileLocation[0] = "Resources";

const iconFile = fs.readFileSync(settingsFileLocation.join("/"), "utf8");
const icons = YAML.parse(iconFile);
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
                                        documentation,
                                        snippet:
                                            '<Garagist.Fontawesome:Component.Icon style="${1|' +
                                            styles +
                                            '|}"${3: size="${4|2xs,xs,sm,lg,xl,2xl,1x,2x,3x,4x,5x,6x,7x,8x,9x,10x|}"} icon="${2|' +
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
    },
);

fs.writeFileSync("Configuration/Settings.ContentBox.yaml", setting);

console.log(`\nWrote ${list.length} icons to ContentBox settings file.\n`);
