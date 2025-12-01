package p5.educationalstrange.eobfuscator.tuple;

public class ETextDefaultObfuscatorSettingTuple extends EObfuscatorSettingTuple{
	public ETextDefaultObfuscatorSettingTuple(String humanLanguage) {
		// assumed to be python like language just for simplicity
		super(50, 50, 2, "#", "", "", humanLanguage, false, true, false, 0.5,
				0.5, 0.5, 2, 2, 2, 6);
	}
}
